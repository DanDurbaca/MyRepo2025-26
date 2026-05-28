<?php
require_once __DIR__ . "/../admin/includes/CommonCode.php";
requireLogin();

$lang = getLanguagePreference($conn);
$tr = fn($en, $fr) => $lang === "fr" ? $fr : $en;
$title = $tr("Chat", "Chat");

$currentUserId = (int)$_SESSION["user_id"];
$msg = "";
// Read tracking is optional so the page still works if the database was not
// migrated with the newest chat column yet.
$hasReadTracking = hasColumn($conn, "chat_room_members", "last_read_message_id");

if (!hasTable($conn, "chat_rooms") || !hasTable($conn, "chat_room_members") || !hasTable($conn, "chat_messages")) {
  require_once __DIR__ . "/../admin/includes/header.php";
  ?>
  <div class="alert alert-warning">
    <?= esc($tr("Chat is not available yet. Please update the database schema first.", "Le chat n'est pas encore disponible. Veuillez d'abord mettre a jour le schema de la base de donnees.")) ?>
  </div>
  <?php
  require_once __DIR__ . "/../admin/includes/footer.php";
  return;
}

$friends = [];
$friendLookup = [];
$stmt = mysqli_prepare($conn, "
  SELECT u.user_id, u.username
  FROM friendships f
  INNER JOIN users u ON u.user_id = f.friend_user_id
  WHERE f.user_id = ?
  ORDER BY u.username
");
mysqli_stmt_bind_param($stmt, "i", $currentUserId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) {
  $friends[] = $row;
  $friendLookup[(int)$row["user_id"]] = $row["username"];
}

$autoStartFriendId = (int)($_GET["start_friend"] ?? 0);

if ($autoStartFriendId > 0 && $_SERVER["REQUEST_METHOD"] !== "POST") {
  if (!isset($friendLookup[$autoStartFriendId])) {
    $msg = $tr("Please choose one of your friends.", "Veuillez choisir un de vos amis.");
  } else {
    $roomId = 0;

    // Direct chats are reused instead of duplicated so one pair of friends
    // always shares the same private conversation history.
    $stmt = mysqli_prepare($conn, "
      SELECT cr.room_id
      FROM chat_rooms cr
      INNER JOIN chat_room_members m1 ON m1.room_id = cr.room_id AND m1.user_id = ?
      INNER JOIN chat_room_members m2 ON m2.room_id = cr.room_id AND m2.user_id = ?
      WHERE cr.is_group = 0
        AND (
          SELECT COUNT(*)
          FROM chat_room_members cm
          WHERE cm.room_id = cr.room_id
        ) = 2
      LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, "ii", $currentUserId, $autoStartFriendId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($res)) {
      $roomId = (int)$row["room_id"];
    }

    if ($roomId === 0) {
      mysqli_begin_transaction($conn);
      try {
        $stmt = mysqli_prepare($conn, "INSERT INTO chat_rooms (name, is_group, created_by) VALUES (NULL, 0, ?)");
        mysqli_stmt_bind_param($stmt, "i", $currentUserId);
        mysqli_stmt_execute($stmt);
        $roomId = (int)mysqli_insert_id($conn);

        if ($hasReadTracking) {
          $stmt = mysqli_prepare($conn, "
            INSERT INTO chat_room_members (room_id, user_id, last_read_message_id)
            VALUES (?, ?, NULL), (?, ?, NULL)
          ");
        } else {
          $stmt = mysqli_prepare($conn, "
            INSERT INTO chat_room_members (room_id, user_id)
            VALUES (?, ?), (?, ?)
          ");
        }
        mysqli_stmt_bind_param($stmt, "iiii", $roomId, $currentUserId, $roomId, $autoStartFriendId);
        mysqli_stmt_execute($stmt);

        mysqli_commit($conn);
      } catch (Throwable $e) {
        mysqli_rollback($conn);
        $roomId = 0;
        $msg = $tr("Could not start the chat.", "Impossible de demarrer le chat.");
      }
    }

    if ($roomId > 0) {
      header("Location: " . appUrl("/user/chat.php") . "?chat=" . $roomId);
      exit();
    }
  }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  checkCsrf();
  $action = $_POST["action"] ?? "";

  if ($action === "start_direct") {
    $friendId = (int)($_POST["friend_user_id"] ?? 0);

    if (!isset($friendLookup[$friendId])) {
      $msg = $tr("Please choose one of your friends.", "Veuillez choisir un de vos amis.");
    } else {
      $roomId = 0;

      $stmt = mysqli_prepare($conn, "
        SELECT cr.room_id
        FROM chat_rooms cr
        INNER JOIN chat_room_members m1 ON m1.room_id = cr.room_id AND m1.user_id = ?
        INNER JOIN chat_room_members m2 ON m2.room_id = cr.room_id AND m2.user_id = ?
        WHERE cr.is_group = 0
          AND (
            SELECT COUNT(*)
            FROM chat_room_members cm
            WHERE cm.room_id = cr.room_id
          ) = 2
        LIMIT 1
      ");
      mysqli_stmt_bind_param($stmt, "ii", $currentUserId, $friendId);
      mysqli_stmt_execute($stmt);
      $res = mysqli_stmt_get_result($stmt);
      if ($row = mysqli_fetch_assoc($res)) {
        $roomId = (int)$row["room_id"];
      }

      if ($roomId === 0) {
        mysqli_begin_transaction($conn);
        try {
          $stmt = mysqli_prepare($conn, "INSERT INTO chat_rooms (name, is_group, created_by) VALUES (NULL, 0, ?)");
          mysqli_stmt_bind_param($stmt, "i", $currentUserId);
          mysqli_stmt_execute($stmt);
          $roomId = (int)mysqli_insert_id($conn);

          if ($hasReadTracking) {
            $stmt = mysqli_prepare($conn, "
              INSERT INTO chat_room_members (room_id, user_id, last_read_message_id)
              VALUES (?, ?, NULL), (?, ?, NULL)
            ");
          } else {
            $stmt = mysqli_prepare($conn, "
              INSERT INTO chat_room_members (room_id, user_id)
              VALUES (?, ?), (?, ?)
            ");
          }
          mysqli_stmt_bind_param($stmt, "iiii", $roomId, $currentUserId, $roomId, $friendId);
          mysqli_stmt_execute($stmt);

          mysqli_commit($conn);
        } catch (Throwable $e) {
          mysqli_rollback($conn);
          $roomId = 0;
          $msg = $tr("Could not start the chat.", "Impossible de demarrer le chat.");
        }
      }

      if ($roomId > 0) {
        header("Location: " . appUrl("/user/chat.php") . "?chat=" . $roomId);
        exit();
      }
    }
  }

  if ($action === "create_group") {
    $name = trim($_POST["group_name"] ?? "");
    $selected = $_POST["friend_ids"] ?? [];
    $friendIds = array_values(array_unique(array_map("intval", is_array($selected) ? $selected : [])));
    $validFriendIds = array_values(array_filter($friendIds, fn($id) => isset($friendLookup[$id])));

    if ($name === "") {
      $msg = $tr("Please enter a group name.", "Veuillez saisir un nom de groupe.");
    } elseif (count($validFriendIds) < 2) {
      $msg = $tr("A group chat must include at least two friends besides you.", "Un groupe de discussion doit inclure au moins deux amis en plus de vous.");
    } else {
      mysqli_begin_transaction($conn);
      try {
        $stmt = mysqli_prepare($conn, "INSERT INTO chat_rooms (name, is_group, created_by) VALUES (?, 1, ?)");
        mysqli_stmt_bind_param($stmt, "si", $name, $currentUserId);
        mysqli_stmt_execute($stmt);
        $roomId = (int)mysqli_insert_id($conn);

        if ($hasReadTracking) {
          $stmt = mysqli_prepare($conn, "INSERT INTO chat_room_members (room_id, user_id, last_read_message_id) VALUES (?, ?, NULL)");
        } else {
          $stmt = mysqli_prepare($conn, "INSERT INTO chat_room_members (room_id, user_id) VALUES (?, ?)");
        }

        mysqli_stmt_bind_param($stmt, "ii", $roomId, $currentUserId);
        mysqli_stmt_execute($stmt);

        foreach ($validFriendIds as $friendId) {
          mysqli_stmt_bind_param($stmt, "ii", $roomId, $friendId);
          mysqli_stmt_execute($stmt);
        }

        mysqli_commit($conn);
        header("Location: " . appUrl("/user/chat.php") . "?chat=" . $roomId);
        exit();
      } catch (Throwable $e) {
        mysqli_rollback($conn);
        $msg = $tr("Could not create the group chat.", "Impossible de creer le groupe de discussion.");
      }
    }
  }

  if ($action === "remove_group_member") {
    $roomId = (int)($_POST["room_id"] ?? 0);
    $memberId = (int)($_POST["member_user_id"] ?? 0);

    if ($roomId <= 0 || $memberId <= 0) {
      $msg = $tr("Invalid group member selection.", "Selection de membre invalide.");
    } else {
      $stmt = mysqli_prepare($conn, "
        SELECT room_id, created_by
        FROM chat_rooms
        WHERE room_id = ? AND is_group = 1
        LIMIT 1
      ");
      mysqli_stmt_bind_param($stmt, "i", $roomId);
      mysqli_stmt_execute($stmt);
      $res = mysqli_stmt_get_result($stmt);
      $room = mysqli_fetch_assoc($res) ?: null;

      if (!$room || (int)$room["created_by"] !== $currentUserId) {
        $msg = $tr("Only the group creator can manage members.", "Seul le createur du groupe peut gerer les membres.");
      } elseif ($memberId === $currentUserId || $memberId === (int)$room["created_by"]) {
        $msg = $tr("The group creator cannot be removed.", "Le createur du groupe ne peut pas etre supprime.");
      } else {
        // The creator keeps control of the room; only other members can be removed.
        $stmt = mysqli_prepare($conn, "
          DELETE FROM chat_room_members
          WHERE room_id = ? AND user_id = ?
          LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, "ii", $roomId, $memberId);
        mysqli_stmt_execute($stmt);

        header("Location: " . appUrl("/user/chat.php") . "?chat=" . $roomId);
        exit();
      }
    }
  }

  if ($action === "delete_group") {
    $roomId = (int)($_POST["room_id"] ?? 0);

    if ($roomId <= 0) {
      $msg = $tr("Invalid group selection.", "Selection de groupe invalide.");
    } else {
      $stmt = mysqli_prepare($conn, "
        SELECT room_id, created_by
        FROM chat_rooms
        WHERE room_id = ? AND is_group = 1
        LIMIT 1
      ");
      mysqli_stmt_bind_param($stmt, "i", $roomId);
      mysqli_stmt_execute($stmt);
      $res = mysqli_stmt_get_result($stmt);
      $room = mysqli_fetch_assoc($res) ?: null;

      if (!$room || (int)$room["created_by"] !== $currentUserId) {
        $msg = $tr("Only the group creator can delete this group.", "Seul le createur du groupe peut supprimer ce groupe.");
      } else {
        $stmt = mysqli_prepare($conn, "DELETE FROM chat_rooms WHERE room_id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $roomId);
        mysqli_stmt_execute($stmt);

        header("Location: " . appUrl("/user/chat.php"));
        exit();
      }
    }
  }

  if ($action === "send_message") {
    $roomId = (int)($_POST["room_id"] ?? 0);
    $body = trim($_POST["body"] ?? "");

    if ($roomId <= 0 || $body === "") {
      $msg = $tr("Please choose a chat and enter a message.", "Veuillez choisir un chat et saisir un message.");
    } else {
      $stmt = mysqli_prepare($conn, "
        SELECT 1
        FROM chat_room_members
        WHERE room_id = ? AND user_id = ?
        LIMIT 1
      ");
      mysqli_stmt_bind_param($stmt, "ii", $roomId, $currentUserId);
      mysqli_stmt_execute($stmt);
      $res = mysqli_stmt_get_result($stmt);

      if (!mysqli_fetch_assoc($res)) {
        $msg = $tr("You do not have access to this chat.", "Vous n'avez pas acces a ce chat.");
      } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO chat_messages (room_id, user_id, body) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iis", $roomId, $currentUserId, $body);
        mysqli_stmt_execute($stmt);

        if ($hasReadTracking) {
          $newMessageId = (int)mysqli_insert_id($conn);
          $stmt = mysqli_prepare($conn, "UPDATE chat_room_members SET last_read_message_id = ? WHERE room_id = ? AND user_id = ?");
          mysqli_stmt_bind_param($stmt, "iii", $newMessageId, $roomId, $currentUserId);
          mysqli_stmt_execute($stmt);
        }

        header("Location: " . appUrl("/user/chat.php") . "?chat=" . $roomId);
        exit();
      }
    }
  }
}

$rooms = [];
if ($hasReadTracking) {
  $stmt = mysqli_prepare($conn, "
    SELECT
      cr.room_id,
      cr.name,
      cr.is_group,
      cr.created_at,
      me.last_read_message_id,
      COALESCE(last_message.body, '') AS last_message_body,
      last_message.created_at AS last_message_at,
      GROUP_CONCAT(u.username ORDER BY u.username SEPARATOR ', ') AS member_names,
      COUNT(DISTINCT unread.message_id) AS unread_count
    FROM chat_rooms cr
    INNER JOIN chat_room_members me ON me.room_id = cr.room_id AND me.user_id = ?
    INNER JOIN chat_room_members crm ON crm.room_id = cr.room_id
    INNER JOIN users u ON u.user_id = crm.user_id
    LEFT JOIN chat_messages last_message ON last_message.message_id = (
      SELECT cm.message_id
      FROM chat_messages cm
      WHERE cm.room_id = cr.room_id
      ORDER BY cm.created_at DESC, cm.message_id DESC
      LIMIT 1
    )
    LEFT JOIN chat_messages unread ON unread.room_id = cr.room_id
      AND unread.user_id <> ?
      AND (me.last_read_message_id IS NULL OR unread.message_id > me.last_read_message_id)
    GROUP BY cr.room_id, cr.name, cr.is_group, cr.created_at, me.last_read_message_id, last_message.body, last_message.created_at
    ORDER BY COALESCE(last_message.created_at, cr.created_at) DESC, cr.room_id DESC
  ");
  mysqli_stmt_bind_param($stmt, "ii", $currentUserId, $currentUserId);
} else {
  $stmt = mysqli_prepare($conn, "
    SELECT
      cr.room_id,
      cr.name,
      cr.is_group,
      cr.created_at,
      COALESCE(last_message.body, '') AS last_message_body,
      last_message.created_at AS last_message_at,
      GROUP_CONCAT(u.username ORDER BY u.username SEPARATOR ', ') AS member_names,
      0 AS unread_count
    FROM chat_rooms cr
    INNER JOIN chat_room_members me ON me.room_id = cr.room_id AND me.user_id = ?
    INNER JOIN chat_room_members crm ON crm.room_id = cr.room_id
    INNER JOIN users u ON u.user_id = crm.user_id
    LEFT JOIN chat_messages last_message ON last_message.message_id = (
      SELECT cm.message_id
      FROM chat_messages cm
      WHERE cm.room_id = cr.room_id
      ORDER BY cm.created_at DESC, cm.message_id DESC
      LIMIT 1
    )
    GROUP BY cr.room_id, cr.name, cr.is_group, cr.created_at, last_message.body, last_message.created_at
    ORDER BY COALESCE(last_message.created_at, cr.created_at) DESC, cr.room_id DESC
  ");
  mysqli_stmt_bind_param($stmt, "i", $currentUserId);
}
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) {
  $rooms[] = $row;
}

$selectedRoomId = (int)($_GET["chat"] ?? 0);
$selectedRoom = null;
$selectedMessages = [];
$selectedMembers = [];

if (isset($_GET["poll"]) && (int)$_GET["poll"] === 1) {
  $roomId = (int)($_GET["chat"] ?? 0);
  $afterId = (int)($_GET["after"] ?? 0);

  header("Content-Type: application/json; charset=UTF-8");

  if ($roomId <= 0) {
    echo json_encode(["ok" => false, "messages" => []]);
    exit();
  }

  $stmt = mysqli_prepare($conn, "
    SELECT 1
    FROM chat_room_members
    WHERE room_id = ? AND user_id = ?
    LIMIT 1
  ");
  mysqli_stmt_bind_param($stmt, "ii", $roomId, $currentUserId);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);

  if (!mysqli_fetch_assoc($res)) {
    echo json_encode(["ok" => false, "messages" => []]);
    exit();
  }

  $messages = [];
  $latestMessageId = $afterId;

  // Polling keeps the floating chat window current without a full page reload.
  $stmt = mysqli_prepare($conn, "
    SELECT cm.message_id, cm.body, cm.created_at, u.user_id, u.username
    FROM chat_messages cm
    INNER JOIN users u ON u.user_id = cm.user_id
    WHERE cm.room_id = ? AND cm.message_id > ?
    ORDER BY cm.created_at ASC, cm.message_id ASC
  ");
  mysqli_stmt_bind_param($stmt, "ii", $roomId, $afterId);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  while ($row = mysqli_fetch_assoc($res)) {
    $latestMessageId = max($latestMessageId, (int)$row["message_id"]);
    $messages[] = [
      "message_id" => (int)$row["message_id"],
      "body" => $row["body"],
      "created_at" => $row["created_at"],
      "username" => $row["username"],
      "is_mine" => ((int)$row["user_id"] === $currentUserId),
    ];
  }

  if ($hasReadTracking && $latestMessageId > 0) {
    $stmt = mysqli_prepare($conn, "
      UPDATE chat_room_members
      SET last_read_message_id = GREATEST(COALESCE(last_read_message_id, 0), ?)
      WHERE room_id = ? AND user_id = ?
    ");
    mysqli_stmt_bind_param($stmt, "iii", $latestMessageId, $roomId, $currentUserId);
    mysqli_stmt_execute($stmt);
  }

  echo json_encode([
    "ok" => true,
    "messages" => $messages,
    "latest_message_id" => $latestMessageId,
  ]);
  exit();
}

if ($selectedRoomId > 0) {
  $stmt = mysqli_prepare($conn, "
    SELECT
      cr.room_id,
      cr.name,
      cr.is_group,
      cr.created_by,
      cr.created_at,
      creator.username AS creator_username
    FROM chat_rooms cr
    INNER JOIN chat_room_members me ON me.room_id = cr.room_id AND me.user_id = ?
    INNER JOIN users creator ON creator.user_id = cr.created_by
    WHERE cr.room_id = ?
    LIMIT 1
  ");
  mysqli_stmt_bind_param($stmt, "ii", $currentUserId, $selectedRoomId);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $selectedRoom = mysqli_fetch_assoc($res) ?: null;

  if (!$selectedRoom) {
    $msg = $tr("Chat not found.", "Chat introuvable.");
  } else {
    $lastMessageId = 0;

    // Member data is loaded separately so the modal can show both the roster
    // and any creator-only management actions.
    $stmt = mysqli_prepare($conn, "
      SELECT u.user_id, u.username
      FROM chat_room_members crm
      INNER JOIN users u ON u.user_id = crm.user_id
      WHERE crm.room_id = ?
      ORDER BY u.username
    ");
    mysqli_stmt_bind_param($stmt, "i", $selectedRoomId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
      $selectedMembers[] = $row;
    }

    $stmt = mysqli_prepare($conn, "
      SELECT cm.message_id, cm.body, cm.created_at, u.user_id, u.username
      FROM chat_messages cm
      INNER JOIN users u ON u.user_id = cm.user_id
      WHERE cm.room_id = ?
      ORDER BY cm.created_at ASC, cm.message_id ASC
    ");
    mysqli_stmt_bind_param($stmt, "i", $selectedRoomId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
      $lastMessageId = (int)$row["message_id"];
      $selectedMessages[] = $row;
    }

    if ($hasReadTracking && $lastMessageId > 0) {
      $stmt = mysqli_prepare($conn, "UPDATE chat_room_members SET last_read_message_id = ? WHERE room_id = ? AND user_id = ?");
      mysqli_stmt_bind_param($stmt, "iii", $lastMessageId, $selectedRoomId, $currentUserId);
      mysqli_stmt_execute($stmt);
      foreach ($rooms as &$room) {
        if ((int)$room["room_id"] === $selectedRoomId) {
          $room["unread_count"] = 0;
          break;
        }
      }
      unset($room);
    }
  }
}

$isSelectedRoomCreator = $selectedRoom && (int)$selectedRoom["created_by"] === $currentUserId;

require_once __DIR__ . "/../admin/includes/header.php";
?>

<section class="soft-panel p-4 p-lg-5 mb-4">
  <div class="row align-items-center g-4">
    <div class="col-lg-8">
      <p class="text-uppercase fw-bold text-secondary small mb-2"><?= esc($tr("Communication", "Communication")) ?></p>
      <h1 class="display-6 fw-bold mb-2"><?= esc($tr("Chat", "Chat")) ?></h1>
    </div>
    <div class="col-lg-4">
      <div class="metric-card">
        <div class="metric-label"><?= esc($tr("Active chats", "Chats actifs")) ?></div>
        <div class="metric-value"><?= count($rooms) ?></div>
      </div>
    </div>
  </div>
</section>

<?php if ($msg !== ""): ?>
  <div class="alert alert-info border-0 shadow-sm"><?= esc($msg) ?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-xl-4">
    <div class="card p-4 mb-3">
      <h2 class="section-title"><?= esc($tr("Start direct chat", "Demarrer un chat direct")) ?></h2>
      <?php if (count($friends) === 0): ?>
        <p class="empty-state"><?= esc($tr("You need at least one friend before you can chat.", "Vous avez besoin d'au moins un ami avant de pouvoir discuter.")) ?></p>
      <?php else: ?>
        <form method="post">
          <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
          <input type="hidden" name="action" value="start_direct">
          <div class="mb-3">
            <label class="form-label"><?= esc($tr("Friend", "Ami")) ?></label>
            <select class="form-select" name="friend_user_id" required>
              <option value=""><?= esc($tr("-- choose --", "-- choisir --")) ?></option>
              <?php foreach ($friends as $friend): ?>
                <option value="<?= (int)$friend["user_id"] ?>"><?= esc($friend["username"]) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button class="btn btn-dark w-100"><?= esc($tr("Open private chat", "Ouvrir un chat prive")) ?></button>
        </form>
      <?php endif; ?>
    </div>

    <div class="card p-4">
      <h2 class="section-title"><?= esc($tr("Create group chat", "Creer un groupe")) ?></h2>
      <?php if (count($friends) < 2): ?>
        <p class="empty-state"><?= esc($tr("You need at least two friends before you can create a group chat.", "Vous avez besoin d'au moins deux amis avant de pouvoir creer un groupe.")) ?></p>
      <?php else: ?>
        <form method="post">
          <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
          <input type="hidden" name="action" value="create_group">
          <div class="mb-3">
            <label class="form-label"><?= esc($tr("Group name", "Nom du groupe")) ?></label>
            <input class="form-control" name="group_name" required>
          </div>
          <div class="mb-3">
            <label class="form-label"><?= esc($tr("Members", "Membres")) ?></label>
            <select class="form-select" name="friend_ids[]" multiple size="8" required>
              <?php foreach ($friends as $friend): ?>
                <option value="<?= (int)$friend["user_id"] ?>"><?= esc($friend["username"]) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button class="btn btn-outline-dark w-100"><?= esc($tr("Create group chat", "Creer le groupe")) ?></button>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-xl-8">
    <div class="card p-4">
      <h2 class="section-title"><?= esc($tr("Your chats", "Vos chats")) ?></h2>

      <?php if (count($rooms) === 0): ?>
        <p class="empty-state"><?= esc($tr("No chats yet.", "Aucun chat pour le moment.")) ?></p>
      <?php else: ?>
        <div class="chat-room-list">
          <?php foreach ($rooms as $room): ?>
            <?php
            $displayName = $room["name"];
            if (!$displayName) {
              $members = array_filter(array_map("trim", explode(",", $room["member_names"] ?? "")));
              $members = array_values(array_filter($members, fn($name) => $name !== ($_SESSION["username"] ?? "")));
              $displayName = implode(", ", $members);
            }
            $isUnread = ((int)($room["unread_count"] ?? 0)) > 0;
            ?>
            <a class="text-decoration-none" href="<?= esc(appUrl('/user/chat.php')) ?>?chat=<?= (int)$room["room_id"] ?>">
              <section class="collection-card chat-room-card<?= $isUnread ? " is-unread" : "" ?>">
                <div class="collection-card-header">
                  <div>
                    <h3 class="collection-card-title mb-1"><?= esc($displayName ?: $tr("Unnamed chat", "Chat sans nom")) ?></h3>
                    <p class="collection-card-description mb-0">
                      <?= esc($room["is_group"] ? $tr("Group chat", "Groupe de discussion") : $tr("Direct chat", "Chat direct")) ?>
                    </p>
                  </div>
                  <div class="collection-card-badge">
                    <?= $isUnread ? esc($tr("New", "Nouveau")) : esc($tr("Open", "Ouvrir")) ?>
                  </div>
                </div>
                <div class="collection-card-meta">
                  <div class="collection-meta-item">
                    <span class="collection-meta-label"><?= esc($tr("Members", "Membres")) ?></span>
                    <span class="collection-meta-value"><?= esc($room["member_names"]) ?></span>
                  </div>
                  <div class="collection-meta-item collection-meta-item-wide">
                    <span class="collection-meta-label"><?= esc($tr("Last message", "Dernier message")) ?></span>
                    <span class="collection-meta-value chat-last-line"><?= esc($room["last_message_body"] !== "" ? $room["last_message_body"] : $tr("No messages yet.", "Aucun message pour le moment.")) ?></span>
                  </div>
                </div>
              </section>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if ($selectedRoom): ?>
  <?php
  $selectedDisplayName = $selectedRoom["name"];
  if (!$selectedDisplayName) {
    $otherMembers = array_values(array_filter(array_map(
      fn($member) => $member["username"],
      $selectedMembers
    ), fn($username) => $username !== ($_SESSION["username"] ?? "")));
    $selectedDisplayName = implode(", ", $otherMembers);
  }
  ?>
  <div class="chat-modal">
    <div class="chat-modal-dialog">
      <div class="card p-4 chat-modal-card">
        <div class="chat-modal-header">
          <div>
            <h2 class="section-title"><?= esc($selectedDisplayName ?: $tr("Unnamed chat", "Chat sans nom")) ?></h2>
            <p class="section-kicker mb-2">
              <?= esc($selectedRoom["is_group"] ? $tr("Group chat", "Groupe de discussion") : $tr("Direct chat", "Chat direct")) ?>
            </p>
            <div>
              <?php foreach ($selectedMembers as $member): ?>
                <span class="chat-member-pill">
                  <?= esc($member["username"]) ?>
                  <?php if ($isSelectedRoomCreator && (int)$selectedRoom["is_group"] === 1 && (int)$member["user_id"] !== $currentUserId): ?>
                    <form method="post" class="d-inline ms-1" onsubmit="return confirm('<?= esc($tr("Remove this user from the group?", "Retirer cet utilisateur du groupe ?")) ?>');">
                      <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
                      <input type="hidden" name="action" value="remove_group_member">
                      <input type="hidden" name="room_id" value="<?= (int)$selectedRoom["room_id"] ?>">
                      <input type="hidden" name="member_user_id" value="<?= (int)$member["user_id"] ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1">x</button>
                    </form>
                  <?php endif; ?>
                </span>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="d-flex gap-2">
            <?php if ($isSelectedRoomCreator && (int)$selectedRoom["is_group"] === 1): ?>
              <form method="post" onsubmit="return confirm('<?= esc($tr("Delete this group?", "Supprimer ce groupe ?")) ?>');">
                <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
                <input type="hidden" name="action" value="delete_group">
                <input type="hidden" name="room_id" value="<?= (int)$selectedRoom["room_id"] ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger"><?= esc($tr("Delete group", "Supprimer le groupe")) ?></button>
              </form>
            <?php endif; ?>
            <a class="btn btn-sm btn-outline-secondary" href="<?= esc(appUrl('/user/chat.php')) ?>">X</a>
          </div>
        </div>

        <div class="chat-modal-body">
          <div id="chatMessages">
            <?php if (count($selectedMessages) === 0): ?>
              <p class="empty-state" id="chatEmptyState"><?= esc($tr("No messages yet. Send the first one.", "Aucun message pour le moment. Envoyez le premier.")) ?></p>
            <?php else: ?>
              <?php foreach ($selectedMessages as $message): ?>
                <?php $isMine = ((int)$message["user_id"] === $currentUserId); ?>
                <section class="chat-message <?= $isMine ? "mine" : "theirs" ?>" data-message-id="<?= (int)$message["message_id"] ?>">
                  <div class="chat-message-meta">
                    <span class="chat-message-author"><?= esc($message["username"]) ?><?= $isMine ? " (" . esc($tr("You", "Vous")) . ")" : "" ?></span>
                    <span class="chat-message-time"><?= esc($message["created_at"]) ?></span>
                  </div>
                  <p class="chat-message-body"><?= nl2br(esc($message["body"])) ?></p>
                </section>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <form method="post" class="chat-composer">
          <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
          <input type="hidden" name="action" value="send_message">
          <input type="hidden" name="room_id" value="<?= (int)$selectedRoom["room_id"] ?>">
          <div class="mb-3">
            <label class="form-label"><?= esc($tr("Message", "Message")) ?></label>
            <textarea class="form-control" name="body" rows="4" required></textarea>
          </div>
          <div class="d-flex justify-content-end gap-2">
            <a class="btn btn-outline-secondary" href="<?= esc(appUrl('/user/chat.php')) ?>"><?= esc($tr("Close", "Fermer")) ?></a>
            <button class="btn btn-dark"><?= esc($tr("Send message", "Envoyer le message")) ?></button>
          </div>
        </form>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . "/../admin/includes/footer.php"; ?>

<?php if ($selectedRoom): ?>
<script>
(() => {
  const container = document.getElementById("chatMessages");
  const scrollBody = document.querySelector(".chat-modal-body");
  if (!container) return;

  let lastMessageId = <?= count($selectedMessages) > 0 ? (int)end($selectedMessages)["message_id"] : 0 ?>;
  const roomId = <?= (int)$selectedRoom["room_id"] ?>;
  const pollUrlBase = <?= json_encode(appUrl('/user/chat.php')) ?>;
  const youLabel = <?= json_encode($tr("You", "Vous")) ?>;
  let polling = false;

  function escapeHtml(text) {
    return String(text)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function nl2br(text) {
    return escapeHtml(text).replace(/\n/g, "<br>");
  }

  function scrollToBottom(force = false) {
    if (!scrollBody) return;
    if (force) {
      scrollBody.scrollTop = scrollBody.scrollHeight;
      return;
    }
    const distanceFromBottom = scrollBody.scrollHeight - scrollBody.scrollTop - scrollBody.clientHeight;
    if (distanceFromBottom < 140) {
      scrollBody.scrollTop = scrollBody.scrollHeight;
    }
  }

  function appendMessage(message) {
    const empty = document.getElementById("chatEmptyState");
    if (empty) empty.remove();

    const section = document.createElement("section");
    section.className = "chat-message " + (message.is_mine ? "mine" : "theirs");
    section.setAttribute("data-message-id", message.message_id);
    section.innerHTML = `
      <div class="chat-message-meta">
        <span class="chat-message-author">${escapeHtml(message.username)}${message.is_mine ? " (" + escapeHtml(youLabel) + ")" : ""}</span>
        <span class="chat-message-time">${escapeHtml(message.created_at)}</span>
      </div>
      <p class="chat-message-body">${nl2br(message.body)}</p>
    `;
    container.appendChild(section);
    scrollToBottom();
  }

  async function poll() {
    if (polling) return;
    polling = true;
    try {
      const url = `${pollUrlBase}?chat=${roomId}&poll=1&after=${lastMessageId}`;
      const res = await fetch(url, { credentials: "same-origin", cache: "no-store" });
      if (!res.ok) return;
      const data = await res.json();
      if (!data.ok || !Array.isArray(data.messages)) return;
      data.messages.forEach((message) => {
        appendMessage(message);
        lastMessageId = Math.max(lastMessageId, Number(message.message_id || 0));
      });
      if (typeof data.latest_message_id !== "undefined") {
        lastMessageId = Math.max(lastMessageId, Number(data.latest_message_id || 0));
      }
    } catch (e) {
      // silent polling failure
    } finally {
      polling = false;
    }
  }

  scrollToBottom(true);
  setInterval(poll, 3000);
})();
</script>
<?php endif; ?>
