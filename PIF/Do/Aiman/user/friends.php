<?php
require_once __DIR__ . "/../admin/includes/CommonCode.php";
/** @var mysqli $conn */
requireLogin();
$lang = getLanguagePreference($conn);
$tr = fn($en, $fr) => $lang === "fr" ? $fr : $en;
$title = $tr("Friends", "Amis");

$msg = "";
$currentUserId = (int)$_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  checkCsrf();
  $action = $_POST["action"] ?? "";

  if ($action === "send_request") {
    $username = trim($_POST["username"] ?? "");

    if ($username === "") {
      $msg = $tr("Please enter a username.", "Veuillez saisir un nom d'utilisateur.");
    } else {
      $stmt = mysqli_prepare($conn, "SELECT user_id, username FROM users WHERE username = ?");
      mysqli_stmt_bind_param($stmt, "s", $username);
      mysqli_stmt_execute($stmt);
      $res = mysqli_stmt_get_result($stmt);

      if (!($target = mysqli_fetch_assoc($res))) {
        $msg = $tr("User not found.", "Utilisateur introuvable.");
      } else {
        $targetId = (int)$target["user_id"];

        if ($targetId === $currentUserId) {
          $msg = $tr("You cannot send a friend request to yourself.", "Vous ne pouvez pas vous envoyer une demande d'ami.");
        } else {
          // Friendship rows represent accepted relationships, so pending requests
          // are checked separately before inserting a new one.
          $stmt = mysqli_prepare($conn, "SELECT 1 FROM friendships WHERE user_id = ? AND friend_user_id = ?");
          mysqli_stmt_bind_param($stmt, "ii", $currentUserId, $targetId);
          mysqli_stmt_execute($stmt);
          $res = mysqli_stmt_get_result($stmt);

          if (mysqli_fetch_assoc($res)) {
            $msg = $tr("You are already friends with this user.", "Vous etes deja amis avec cet utilisateur.");
          } else {
            $stmt = mysqli_prepare($conn, "
              SELECT status
              FROM friend_requests
              WHERE
                (sender_user_id = ? AND receiver_user_id = ? AND status = 'pending')
                OR
                (sender_user_id = ? AND receiver_user_id = ? AND status = 'pending')
              LIMIT 1
            ");
            mysqli_stmt_bind_param($stmt, "iiii", $currentUserId, $targetId, $targetId, $currentUserId);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);

            if (mysqli_fetch_assoc($res)) {
              $msg = $tr("A friend request is already pending between you and this user.", "Une demande d'ami est deja en attente entre vous et cet utilisateur.");
            } else {
              $stmt = mysqli_prepare($conn, "
                INSERT INTO friend_requests (sender_user_id, receiver_user_id, status)
                VALUES (?, ?, 'pending')
              ");
              mysqli_stmt_bind_param($stmt, "ii", $currentUserId, $targetId);

              if (mysqli_stmt_execute($stmt)) {
                $msg = $tr("Friend request sent to ", "Demande d'ami envoyee a ") . $target["username"] . ".";
              } else {
                $msg = $tr("Could not send the friend request.", "Impossible d'envoyer la demande d'ami.");
              }
            }
          }
        }
      }
    }
  }

  if ($action === "accept_request") {
    $requestId = (int)($_POST["request_id"] ?? 0);

    $stmt = mysqli_prepare($conn, "
      SELECT request_id, sender_user_id, receiver_user_id
      FROM friend_requests
      WHERE request_id = ? AND receiver_user_id = ? AND status = 'pending'
      LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, "ii", $requestId, $currentUserId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($request = mysqli_fetch_assoc($res)) {
      $senderId = (int)$request["sender_user_id"];

      mysqli_begin_transaction($conn);

      try {
        // Accepting a request creates the friendship in both directions so the
        // rest of the application can query a simple one-way friends list.
        $stmt = mysqli_prepare($conn, "
          UPDATE friend_requests
          SET status = 'accepted', responded_at = NOW()
          WHERE request_id = ? AND status = 'pending'
        ");
        mysqli_stmt_bind_param($stmt, "i", $requestId);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) !== 1) {
          throw new Exception("The request could not be updated.");
        }

        $stmt = mysqli_prepare($conn, "
          INSERT INTO friendships (user_id, friend_user_id)
          VALUES (?, ?), (?, ?)
        ");
        mysqli_stmt_bind_param($stmt, "iiii", $currentUserId, $senderId, $senderId, $currentUserId);
        mysqli_stmt_execute($stmt);

        mysqli_commit($conn);
        $msg = $tr("Friend request accepted.", "Demande d'ami acceptee.");
      } catch (Throwable $e) {
        mysqli_rollback($conn);
        $msg = $tr("Could not accept the friend request.", "Impossible d'accepter la demande d'ami.");
      }
    } else {
      $msg = $tr("Friend request not found.", "Demande d'ami introuvable.");
    }
  }

  if ($action === "reject_request") {
    $requestId = (int)($_POST["request_id"] ?? 0);

    $stmt = mysqli_prepare($conn, "
      UPDATE friend_requests
      SET status = 'rejected', responded_at = NOW()
      WHERE request_id = ? AND receiver_user_id = ? AND status = 'pending'
    ");
    mysqli_stmt_bind_param($stmt, "ii", $requestId, $currentUserId);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) === 1) {
      $msg = $tr("Friend request rejected.", "Demande d'ami refusee.");
    } else {
      $msg = $tr("Friend request not found.", "Demande d'ami introuvable.");
    }
  }

  if ($action === "remove_friend") {
    $friendId = (int)($_POST["friend_user_id"] ?? 0);

    mysqli_begin_transaction($conn);

    try {
      // Removing a friendship also clears pending requests and shared
      // collections between the two users to keep the collaboration model consistent.
      $stmt = mysqli_prepare($conn, "
        DELETE FROM friendships
        WHERE (user_id = ? AND friend_user_id = ?)
           OR (user_id = ? AND friend_user_id = ?)
      ");
      mysqli_stmt_bind_param($stmt, "iiii", $currentUserId, $friendId, $friendId, $currentUserId);
      mysqli_stmt_execute($stmt);

      $stmt = mysqli_prepare($conn, "
        UPDATE friend_requests
        SET status = 'rejected', responded_at = NOW()
        WHERE
          (
            sender_user_id = ? AND receiver_user_id = ?
            AND status = 'pending'
          )
          OR
          (
            sender_user_id = ? AND receiver_user_id = ?
            AND status = 'pending'
          )
      ");
      mysqli_stmt_bind_param($stmt, "iiii", $currentUserId, $friendId, $friendId, $currentUserId);
      mysqli_stmt_execute($stmt);

      $stmt = mysqli_prepare($conn, "
        DELETE cs
        FROM collection_shares cs
        INNER JOIN collections c ON c.collection_id = cs.collection_id
        WHERE
          (c.user_id = ? AND cs.user_id = ?)
          OR
          (c.user_id = ? AND cs.user_id = ?)
      ");
      mysqli_stmt_bind_param($stmt, "iiii", $currentUserId, $friendId, $friendId, $currentUserId);
      mysqli_stmt_execute($stmt);

      mysqli_commit($conn);
      $msg = $tr("Friendship ended.", "Amitie terminee.");
    } catch (Throwable $e) {
      mysqli_rollback($conn);
      $msg = $tr("Could not end the friendship.", "Impossible de mettre fin a l'amitie.");
    }
  }
}

$incomingRequests = [];
$stmt = mysqli_prepare($conn, "
  SELECT fr.request_id, fr.created_at, u.user_id, u.username
  FROM friend_requests fr
  INNER JOIN users u ON u.user_id = fr.sender_user_id
  WHERE fr.receiver_user_id = ? AND fr.status = 'pending'
  ORDER BY fr.created_at DESC
");
mysqli_stmt_bind_param($stmt, "i", $currentUserId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) $incomingRequests[] = $row;

$outgoingRequests = [];
$stmt = mysqli_prepare($conn, "
  SELECT fr.request_id, fr.created_at, u.user_id, u.username
  FROM friend_requests fr
  INNER JOIN users u ON u.user_id = fr.receiver_user_id
  WHERE fr.sender_user_id = ? AND fr.status = 'pending'
  ORDER BY fr.created_at DESC
");
mysqli_stmt_bind_param($stmt, "i", $currentUserId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) $outgoingRequests[] = $row;

$requestUpdates = [];
$stmt = mysqli_prepare($conn, "
  SELECT fr.request_id, fr.status, fr.responded_at, u.username
  FROM friend_requests fr
  INNER JOIN users u ON u.user_id = fr.receiver_user_id
  WHERE fr.sender_user_id = ? AND fr.status IN ('accepted', 'rejected')
  ORDER BY fr.responded_at DESC, fr.request_id DESC
  LIMIT 10
");
mysqli_stmt_bind_param($stmt, "i", $currentUserId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) $requestUpdates[] = $row;

$friends = [];
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
while ($row = mysqli_fetch_assoc($res)) $friends[] = $row;

$incomingCount = count($incomingRequests);
$outgoingCount = count($outgoingRequests);
$updatesCount = count($requestUpdates);
$friendsCount = count($friends);

require_once __DIR__ . "/../admin/includes/header.php";
?>

<section class="soft-panel p-4 p-lg-5 mb-4">
  <div class="row align-items-center g-4">
    <div class="col-lg-7">
      <p class="text-uppercase fw-bold text-secondary small mb-2"><?= esc($tr("Community", "Communaute")) ?></p>
      <h1 class="display-6 fw-bold mb-2"><?= esc($tr("Friends", "Amis")) ?></h1>
    </div>
    <div class="col-lg-5">
      <div class="row g-3">
        <div class="col-6">
          <div class="metric-card">
            <div class="metric-label"><?= esc($tr("Incoming", "Entrantes")) ?></div>
            <div class="metric-value"><?= $incomingCount ?></div>
          </div>
        </div>
        <div class="col-6">
          <div class="metric-card">
            <div class="metric-label"><?= esc($tr("Friends", "Amis")) ?></div>
            <div class="metric-value"><?= $friendsCount ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if ($msg !== ""): ?>
  <div class="alert alert-info border-0 shadow-sm"><?= esc($msg) ?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-xl-4">
    <div class="card p-4">
      <div class="card-header-inline">
        <div>
          <h2 class="section-title"><?= esc($tr("Send friend request", "Envoyer une demande d'ami")) ?></h2>
        </div>
        <span class="badge text-bg-dark"><?= esc($tr("New", "Nouveau")) ?></span>
      </div>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
        <input type="hidden" name="action" value="send_request">

        <label class="form-label"><?= esc($tr("Username", "Nom d'utilisateur")) ?></label>
        <input class="form-control form-control-lg mb-3" name="username" placeholder="<?= esc($tr("Enter username", "Saisissez le nom d'utilisateur")) ?>" required>

        <button class="btn btn-dark btn-lg w-100"><?= esc($tr("Send request", "Envoyer la demande")) ?></button>
      </form>
    </div>
  </div>

  <div class="col-xl-8 friends-stack">
    <div class="card p-4">
      <div class="card-header-inline">
        <div>
          <h2 class="section-title"><?= esc($tr("Incoming requests", "Demandes entrantes")) ?></h2>
        </div>
        <span class="badge rounded-pill text-bg-primary"><?= $incomingCount ?></span>
      </div>

      <?php if ($incomingCount === 0): ?>
        <p class="empty-state"><?= esc($tr("No incoming requests.", "Aucune demande entrante.")) ?></p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead>
              <tr>
                <th><?= esc($tr("Username", "Nom d'utilisateur")) ?></th>
                <th><?= esc($tr("Sent", "Envoyee")) ?></th>
                <th><?= esc($tr("Actions", "Actions")) ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($incomingRequests as $request): ?>
                <tr>
                  <td class="fw-semibold"><?= esc($request["username"]) ?></td>
                  <td><?= esc($request["created_at"]) ?></td>
                  <td>
                    <div class="action-group">
                      <form method="post">
                        <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
                        <input type="hidden" name="action" value="accept_request">
                        <input type="hidden" name="request_id" value="<?= (int)$request["request_id"] ?>">
                        <button class="btn btn-sm btn-success"><?= esc($tr("Accept", "Accepter")) ?></button>
                      </form>

                      <form method="post">
                        <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
                        <input type="hidden" name="action" value="reject_request">
                        <input type="hidden" name="request_id" value="<?= (int)$request["request_id"] ?>">
                        <button class="btn btn-sm btn-outline-danger"><?= esc($tr("Reject", "Refuser")) ?></button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <div class="card p-4">
      <div class="card-header-inline">
        <div>
          <h2 class="section-title"><?= esc($tr("Outgoing requests", "Demandes sortantes")) ?></h2>
        </div>
        <span class="badge rounded-pill text-bg-secondary"><?= $outgoingCount ?></span>
      </div>

      <?php if ($outgoingCount === 0): ?>
        <p class="empty-state"><?= esc($tr("No pending requests sent by you.", "Aucune demande en attente envoyee par vous.")) ?></p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead>
              <tr>
                <th><?= esc($tr("Username", "Nom d'utilisateur")) ?></th>
                <th><?= esc($tr("Sent", "Envoyee")) ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($outgoingRequests as $request): ?>
                <tr>
                  <td class="fw-semibold"><?= esc($request["username"]) ?></td>
                  <td><?= esc($request["created_at"]) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <div class="card p-4">
      <div class="card-header-inline">
        <div>
          <h2 class="section-title"><?= esc($tr("Request updates", "Mises a jour des demandes")) ?></h2>
        </div>
        <span class="badge rounded-pill text-bg-light"><?= $updatesCount ?></span>
      </div>

      <?php if ($updatesCount === 0): ?>
        <p class="empty-state"><?= esc($tr("No updates yet.", "Aucune mise a jour pour le moment.")) ?></p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead>
              <tr>
                <th><?= esc($tr("Username", "Nom d'utilisateur")) ?></th>
                <th><?= esc($tr("Status", "Statut")) ?></th>
                <th><?= esc($tr("Updated", "Mise a jour")) ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($requestUpdates as $update): ?>
                <tr>
                  <td class="fw-semibold"><?= esc($update["username"]) ?></td>
                  <td>
                    <?php if ($update["status"] === "accepted"): ?>
                      <span class="badge bg-success"><?= esc($tr("Accepted", "Acceptee")) ?></span>
                    <?php else: ?>
                      <span class="badge bg-danger"><?= esc($tr("Rejected", "Refusee")) ?></span>
                    <?php endif; ?>
                  </td>
                  <td><?= esc($update["responded_at"] ?? "-") ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <div class="card p-4">
      <div class="card-header-inline">
        <div>
          <h2 class="section-title"><?= esc($tr("My friends", "Mes amis")) ?></h2>
        </div>
        <span class="badge rounded-pill text-bg-success"><?= $friendsCount ?></span>
      </div>

      <?php if ($friendsCount === 0): ?>
        <p class="empty-state"><?= esc($tr("No friends yet.", "Aucun ami pour le moment.")) ?></p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead>
              <tr>
                <th><?= esc($tr("Username", "Nom d'utilisateur")) ?></th>
                <th><?= esc($tr("Actions", "Actions")) ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($friends as $friend): ?>
                <tr>
                  <td class="fw-semibold"><?= esc($friend["username"]) ?></td>
                  <td class="text-end">
                    <div class="action-group justify-content-end">
                      <a class="btn btn-sm btn-outline-secondary" href="<?= esc(appUrl('/user/chat.php')) ?>?start_friend=<?= (int)$friend["user_id"] ?>"><?= esc($tr("Chat", "Chat")) ?></a>
                      <form method="post" onsubmit="return confirm('<?= esc($tr("End this friendship?", "Mettre fin a cette amitie ?")) ?>');">
                        <input type="hidden" name="csrf" value="<?= esc(csrfToken()) ?>">
                        <input type="hidden" name="action" value="remove_friend">
                        <input type="hidden" name="friend_user_id" value="<?= (int)$friend["user_id"] ?>">
                        <button class="btn btn-sm btn-outline-danger"><?= esc($tr("Remove", "Retirer")) ?></button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . "/../admin/includes/footer.php";
 ?>
