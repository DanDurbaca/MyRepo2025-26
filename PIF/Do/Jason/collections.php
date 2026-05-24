<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="MyCss.css?<?=time();?>">
    <title>Collections</title>
</head>
<body class="stations-page">
<?php
include_once("commonphp.php");

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: index.php');
    exit;
}

// Create sharing table if it doesn't exist yet
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS Collection_Shares (
    collection_ID INT NOT NULL,
    shared_with_user INT NOT NULL,
    PRIMARY KEY (collection_ID, shared_with_user),
    FOREIGN KEY (collection_ID) REFERENCES Collection(collection_ID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (shared_with_user) REFERENCES `User`(user_ID) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$msg = '';
$err = '';

// ── HANDLE POST ACTIONS 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Add a new collection
    if (isset($_POST['add_collection'])) {
        $stationId = (int)$_POST['station_id'];
        $name      = trim($_POST['collection_name']);
        $startDt   = $_POST['start_date'] . ' ' . $_POST['start_time'] . ':00';
        $endDt     = $_POST['end_date']   . ' ' . $_POST['end_time']   . ':59';

        // Verify the station belongs to the user
        $chk = mysqli_prepare($conn, "SELECT serial_number FROM Station WHERE serial_number = ? AND user_station = ?");
        mysqli_stmt_bind_param($chk, 'ii', $stationId, $userId);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if (mysqli_stmt_num_rows($chk) > 0) {
            mysqli_stmt_close($chk);

            // Create the collection
            $ins = mysqli_prepare($conn, "INSERT INTO Collection (collection_name, station_description) VALUES (?, '')");
            mysqli_stmt_bind_param($ins, 's', $name);
            mysqli_stmt_execute($ins);
            $collectionId = mysqli_insert_id($conn);
            mysqli_stmt_close($ins);

            // Link collection to user
            $lnk = mysqli_prepare($conn, "INSERT INTO User_Collections (user, collection_ID) VALUES (?, ?)");
            mysqli_stmt_bind_param($lnk, 'ii', $userId, $collectionId);
            mysqli_stmt_execute($lnk);
            mysqli_stmt_close($lnk);

            // Add measurements in range to the collection
            $mStmt = mysqli_prepare($conn, "SELECT measurement_ID FROM Measurement WHERE station = ? AND timestamp_Measurement BETWEEN ? AND ?");
            mysqli_stmt_bind_param($mStmt, 'iss', $stationId, $startDt, $endDt);
            mysqli_stmt_execute($mStmt);
            $mResult = mysqli_stmt_get_result($mStmt);
            while ($mRow = mysqli_fetch_assoc($mResult)) {
                $ins2 = mysqli_prepare($conn, "INSERT INTO Collection_Measurements (collection_ID, measurement) VALUES (?, ?)");
                mysqli_stmt_bind_param($ins2, 'ii', $collectionId, $mRow['measurement_ID']);
                mysqli_stmt_execute($ins2);
                mysqli_stmt_close($ins2);
            }
            mysqli_stmt_close($mStmt);
            $msg = 'Collection created.';
        } else {
            mysqli_stmt_close($chk);
            $err = 'Invalid station.';
        }

    // Rename a collection (only owner)
    } elseif (isset($_POST['rename_collection'])) {
        $collectionId = (int)$_POST['collection_id'];
        $name         = trim($_POST['new_name']);
        $chk = mysqli_prepare($conn, "SELECT c.collection_ID FROM Collection c JOIN User_Collections uc ON c.collection_ID = uc.collection_ID WHERE c.collection_ID = ? AND uc.user = ?");
        mysqli_stmt_bind_param($chk, 'ii', $collectionId, $userId);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if (mysqli_stmt_num_rows($chk) > 0) {
            mysqli_stmt_close($chk);
            $upd = mysqli_prepare($conn, "UPDATE Collection SET collection_name = ? WHERE collection_ID = ?");
            mysqli_stmt_bind_param($upd, 'si', $name, $collectionId);
            mysqli_stmt_execute($upd);
            mysqli_stmt_close($upd);
            $msg = 'Collection renamed.';
        } else {
            mysqli_stmt_close($chk);
            $err = 'Invalid collection.';
        }

    // Delete a collection (only owner)
    } elseif (isset($_POST['delete_collection'])) {
        $collectionId = (int)$_POST['collection_id'];
        $chk = mysqli_prepare($conn, "SELECT c.collection_ID FROM Collection c JOIN User_Collections uc ON c.collection_ID = uc.collection_ID WHERE c.collection_ID = ? AND uc.user = ?");
        mysqli_stmt_bind_param($chk, 'ii', $collectionId, $userId);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if (mysqli_stmt_num_rows($chk) > 0) {
            mysqli_stmt_close($chk);
            foreach ([
                "DELETE FROM Collection_Shares WHERE collection_ID = ?",
                "DELETE FROM Collection_Measurements WHERE collection_ID = ?",
                "DELETE FROM User_Collections WHERE collection_ID = ?",
                "DELETE FROM Collection WHERE collection_ID = ?"
            ] as $sql) {
                $s = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($s, 'i', $collectionId);
                mysqli_stmt_execute($s);
                mysqli_stmt_close($s);
            }
            $msg = 'Collection deleted.';
        } else {
            mysqli_stmt_close($chk);
            $err = 'Invalid collection.';
        }

    // Share a collection with a friend
    } elseif (isset($_POST['share_collection'])) {
        $collectionId = (int)$_POST['collection_id'];
        $friendId     = (int)$_POST['friend_id'];

        // Verify the collection belongs to the user
        $chk = mysqli_prepare($conn, "SELECT c.collection_ID FROM Collection c JOIN User_Collections uc ON c.collection_ID = uc.collection_ID WHERE c.collection_ID = ? AND uc.user = ?");
        mysqli_stmt_bind_param($chk, 'ii', $collectionId, $userId);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if (mysqli_stmt_num_rows($chk) > 0) {
            mysqli_stmt_close($chk);
            // Verify the target is actually a friend
            $fchk = mysqli_prepare($conn, "SELECT friend_id FROM Friendship WHERE user_id = ? AND friend_id = ?");
            mysqli_stmt_bind_param($fchk, 'ii', $userId, $friendId);
            mysqli_stmt_execute($fchk);
            mysqli_stmt_store_result($fchk);
            if (mysqli_stmt_num_rows($fchk) > 0) {
                mysqli_stmt_close($fchk);
                $ins = mysqli_prepare($conn, "INSERT IGNORE INTO Collection_Shares (collection_ID, shared_with_user) VALUES (?, ?)");
                mysqli_stmt_bind_param($ins, 'ii', $collectionId, $friendId);
                mysqli_stmt_execute($ins);
                mysqli_stmt_close($ins);
                $msg = 'Collection shared.';
            } else {
                mysqli_stmt_close($fchk);
                $err = 'You can only share with friends.';
            }
        } else {
            mysqli_stmt_close($chk);
            $err = 'Invalid collection.';
        }

    // Unshare a collection
    } elseif (isset($_POST['unshare_collection'])) {
        $collectionId = (int)$_POST['collection_id'];
        $friendId     = (int)$_POST['friend_id'];

        // Verify the collection belongs to the user
        $chk = mysqli_prepare($conn, "SELECT c.collection_ID FROM Collection c JOIN User_Collections uc ON c.collection_ID = uc.collection_ID WHERE c.collection_ID = ? AND uc.user = ?");
        mysqli_stmt_bind_param($chk, 'ii', $collectionId, $userId);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if (mysqli_stmt_num_rows($chk) > 0) {
            mysqli_stmt_close($chk);
            $del = mysqli_prepare($conn, "DELETE FROM Collection_Shares WHERE collection_ID = ? AND shared_with_user = ?");
            mysqli_stmt_bind_param($del, 'ii', $collectionId, $friendId);
            mysqli_stmt_execute($del);
            mysqli_stmt_close($del);
            $msg = 'Sharing removed.';
        } else {
            mysqli_stmt_close($chk);
            $err = 'Invalid collection.';
        }
    }
}

// ── LOAD DATA ────────────────────────────────────────────────────────────────

// User's own collections
$ownCollections = [];
$r = mysqli_prepare($conn, "SELECT c.collection_ID, c.collection_name FROM Collection c JOIN User_Collections uc ON c.collection_ID = uc.collection_ID WHERE uc.user = ? ORDER BY c.collection_name");
mysqli_stmt_bind_param($r, 'i', $userId);
mysqli_stmt_execute($r);
$res = mysqli_stmt_get_result($r);
while ($row = mysqli_fetch_assoc($res)) { $ownCollections[] = $row; }
mysqli_stmt_close($r);

// For each own collection: who is it already shared with?
$sharedWith = []; // collection_ID => [array of users]
if (count($ownCollections) > 0) {
    foreach ($ownCollections as $col) {
        $cId = (int)$col['collection_ID'];
        $sw  = mysqli_prepare($conn, "SELECT u.user_ID, u.UName FROM Collection_Shares cs JOIN `User` u ON cs.shared_with_user = u.user_ID WHERE cs.collection_ID = ?");
        mysqli_stmt_bind_param($sw, 'i', $cId);
        mysqli_stmt_execute($sw);
        $swRes = mysqli_stmt_get_result($sw);
        $sharedWith[$cId] = [];
        while ($swRow = mysqli_fetch_assoc($swRes)) { $sharedWith[$cId][] = $swRow; }
        mysqli_stmt_close($sw);
    }
}

// User's friends (to populate share dropdown)
$friends = [];
$fr = mysqli_prepare($conn, "SELECT u.user_ID, u.UName FROM Friendship f JOIN `User` u ON f.friend_id = u.user_ID WHERE f.user_id = ? ORDER BY u.UName");
mysqli_stmt_bind_param($fr, 'i', $userId);
mysqli_stmt_execute($fr);
$frRes = mysqli_stmt_get_result($fr);
while ($row = mysqli_fetch_assoc($frRes)) { $friends[] = $row; }
mysqli_stmt_close($fr);

// Collections shared WITH this user (by others)
$sharedWithMe = [];
$sw2 = mysqli_prepare($conn, "SELECT c.collection_ID, c.collection_name, u.UName AS owner,
    GROUP_CONCAT(m.temperature ORDER BY m.timestamp_Measurement SEPARATOR '|') AS temps,
    GROUP_CONCAT(m.humidity ORDER BY m.timestamp_Measurement SEPARATOR '|') AS humids,
    GROUP_CONCAT(m.airpressure ORDER BY m.timestamp_Measurement SEPARATOR '|') AS pressures,
    GROUP_CONCAT(m.lightintensity ORDER BY m.timestamp_Measurement SEPARATOR '|') AS lights,
    GROUP_CONCAT(m.airquality ORDER BY m.timestamp_Measurement SEPARATOR '|') AS airqualities,
    GROUP_CONCAT(m.timestamp_Measurement ORDER BY m.timestamp_Measurement SEPARATOR '|') AS timestamps
    FROM Collection_Shares cs
    JOIN Collection c ON cs.collection_ID = c.collection_ID
    JOIN User_Collections uc ON c.collection_ID = uc.collection_ID
    JOIN `User` u ON uc.user = u.user_ID
    LEFT JOIN Collection_Measurements cm ON c.collection_ID = cm.collection_ID
    LEFT JOIN Measurement m ON cm.measurement = m.measurement_ID
    WHERE cs.shared_with_user = ?
    GROUP BY c.collection_ID, c.collection_name, u.UName
    ORDER BY c.collection_name");
mysqli_stmt_bind_param($sw2, 'i', $userId);
mysqli_stmt_execute($sw2);
$sw2Res = mysqli_stmt_get_result($sw2);
while ($row = mysqli_fetch_assoc($sw2Res)) { $sharedWithMe[] = $row; }
mysqli_stmt_close($sw2);

// User's own stations for the add-collection form
$myStations = [];
$st = mysqli_prepare($conn, "SELECT serial_number, station_name FROM Station WHERE user_station = ? ORDER BY station_name");
mysqli_stmt_bind_param($st, 'i', $userId);
mysqli_stmt_execute($st);
$stRes = mysqli_stmt_get_result($st);
while ($row = mysqli_fetch_assoc($stRes)) { $myStations[] = $row; }
mysqli_stmt_close($st);
?>

<div class="container">
    <h1 class="Title">Collections</h1>
    <p class="lead">Manage and share your collections.</p>

    <?php if ($msg !== ''): ?>
        <div class="alert" style="background:#d1fae5;border-color:#6ee7b7;color:#065f46;"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if ($err !== ''): ?>
        <div class="alert" style="background:#fee2e2;border-color:#fca5a5;color:#991b1b;"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <!-- ── YOUR COLLECTIONS ─────────────────────────────────────────────── -->
    <h2>Your Collections</h2>
    <?php if (count($ownCollections) > 0): ?>
        <?php foreach ($ownCollections as $col): ?>
        <div class="station-card">
            <h3><?= htmlspecialchars($col['collection_name'], ENT_QUOTES, 'UTF-8') ?></h3>

            <!-- Rename form -->
            <form method="post" class="inline-form">
                <input type="hidden" name="collection_id" value="<?= (int)$col['collection_ID'] ?>">
                <label>New name</label>
                <input type="text" name="new_name" required style="width:200px;">
                <button type="submit" name="rename_collection">Rename</button>
            </form>

            <!-- Delete form -->
            <form method="post" class="inline-form" onsubmit="return confirm('Delete this collection?');">
                <input type="hidden" name="collection_id" value="<?= (int)$col['collection_ID'] ?>">
                <button type="submit" name="delete_collection" style="background:#dc2626;">Delete</button>
            </form>

            <!-- Share with a friend -->
            <?php if (count($friends) > 0): ?>
            <form method="post" class="inline-form">
                <input type="hidden" name="collection_id" value="<?= (int)$col['collection_ID'] ?>">
                <label>Share with</label>
                <select name="friend_id" required>
                    <option value="">Select a friend</option>
                    <?php foreach ($friends as $f): ?>
                        <option value="<?= (int)$f['user_ID'] ?>"><?= htmlspecialchars($f['UName'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="share_collection">Share</button>
            </form>
            <?php endif; ?>

            <!-- Currently shared with -->
            <?php if (count($sharedWith[(int)$col['collection_ID']]) > 0): ?>
            <div style="margin-top:0.75rem;">
                <strong>Shared with:</strong>
                <?php foreach ($sharedWith[(int)$col['collection_ID']] as $sw): ?>
                <form method="post" style="display:inline-flex;align-items:center;gap:0.4rem;margin:0.2rem 0;">
                    <input type="hidden" name="collection_id" value="<?= (int)$col['collection_ID'] ?>">
                    <input type="hidden" name="friend_id" value="<?= (int)$sw['user_ID'] ?>">
                    <span><?= htmlspecialchars($sw['UName'], ENT_QUOTES, 'UTF-8') ?></span>
                    <button type="submit" name="unshare_collection" style="background:#dc2626;padding:0.3rem 0.6rem;font-size:0.8rem;border-radius:6px;">Unshare</button>
                </form>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert">No collections yet.</div>
    <?php endif; ?>

    <!-- ── ADD COLLECTION ───────────────────────────────────────────────── -->
    <div class="section-card">
        <h2>Add Collection</h2>
        <?php if (count($myStations) > 0): ?>
        <form method="post">
            <div class="field-row">
                <label>Collection Name</label>
                <input type="text" name="collection_name" required>
            </div>
            <div class="field-row">
                <label>Station</label>
                <select name="station_id" required>
                    <?php foreach ($myStations as $s): ?>
                        <option value="<?= (int)$s['serial_number'] ?>"><?= htmlspecialchars($s['station_name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field-row">
                <label>Start Date &amp; Time</label>
                <input type="date" name="start_date" required>
                <input type="time" name="start_time" required>
            </div>
            <div class="field-row">
                <label>End Date &amp; Time</label>
                <input type="date" name="end_date" required>
                <input type="time" name="end_time" required>
            </div>
            <div class="button-row">
                <button type="submit" name="add_collection">Add Collection</button>
            </div>
        </form>
        <?php else: ?>
            <div class="alert">You have no stations assigned. Register a station first.</div>
        <?php endif; ?>
    </div>

    <!-- ── COLLECTIONS SHARED WITH YOU ──────────────────────────────────── -->
    <h2>Shared With You</h2>
    <?php if (count($sharedWithMe) > 0): ?>
        <?php foreach ($sharedWithMe as $col): ?>
        <div class="station-card">
            <h3><?= htmlspecialchars($col['collection_name'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="note">Shared by: <?= htmlspecialchars($col['owner'], ENT_QUOTES, 'UTF-8') ?></p>

            <?php if ($col['timestamps']): ?>
            <table class="measurement-table">
                <tr><th>Timestamp</th><th>Temp (°C)</th><th>Humidity (%)</th><th>Pressure</th><th>Light</th><th>Air Quality</th></tr>
                <?php
                $timestamps   = explode('|', $col['timestamps']);
                $temps        = explode('|', $col['temps'] ?? '');
                $humids       = explode('|', $col['humids'] ?? '');
                $pressures    = explode('|', $col['pressures'] ?? '');
                $lights       = explode('|', $col['lights'] ?? '');
                $airqualities = explode('|', $col['airqualities'] ?? '');
                foreach ($timestamps as $i => $ts):
                ?>
                <tr>
                    <td><?= htmlspecialchars($ts, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($temps[$i]        ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($humids[$i]       ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($pressures[$i]    ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($lights[$i]       ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($airqualities[$i] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php else: ?>
                <div class="alert">This collection has no measurements.</div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert">No collections have been shared with you.</div>
    <?php endif; ?>

</div>
</body>
</html>
