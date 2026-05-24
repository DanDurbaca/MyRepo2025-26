<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="MyCss.css?<?=time();?>">
    <title>Admin</title>
    <style>
        body.stations-page .admin-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        body.stations-page .admin-tab {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 8px;
            background: #e5e7eb;
            color: #111827;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        body.stations-page .admin-tab.active,
        body.stations-page .admin-tab:hover {
            background: #2563eb;
            color: #fff;
        }
        body.stations-page .admin-section { display: none; }
        body.stations-page .admin-section.active { display: block; }
        body.stations-page table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            font-size: 0.92rem;
        }
        body.stations-page table th,
        body.stations-page table td {
            padding: 0.65rem 0.75rem;
            border: 1px solid #e5e7eb;
            text-align: left;
            vertical-align: middle;
        }
        body.stations-page table th {
            background: #eff6ff;
            font-weight: 700;
        }
        body.stations-page table tr:nth-child(even) { background: #f9fafb; }
        body.stations-page .btn-danger {
            background: #dc2626;
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 0.4rem 0.8rem;
            cursor: pointer;
            font-size: 0.85rem;
        }
        body.stations-page .btn-danger:hover { background: #b91c1c; }
        body.stations-page .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
            border-radius: 7px;
        }
        body.stations-page input[type="text"],
        body.stations-page input[type="email"],
        body.stations-page input[type="password"],
        body.stations-page input[type="number"],
        body.stations-page select {
            padding: 0.5rem 0.7rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.92rem;
            width: 100%;
            box-sizing: border-box;
        }
        body.stations-page .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem 1rem;
            margin-bottom: 1rem;
        }
        body.stations-page .form-grid label { font-weight: 600; display: block; margin-bottom: 0.25rem; }
        @media (max-width: 600px) {
            body.stations-page .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="stations-page">
<?php
include_once("commonphp.php");

// Only admins can access this page
if (empty($_SESSION['is_admin'])) {
    header('Location: Homepage.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$msg = '';
$err = '';

// ─── HANDLE ALL POST ACTIONS ────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── USERS ────────────────────────────────────────────────────────────────

    // Create a new user
    if ($action === 'create_user') {
        $uname  = trim($_POST['new_uname']  ?? '');
        $email  = trim($_POST['new_email']  ?? '');
        $fname  = trim($_POST['new_fname']  ?? '');
        $lname  = trim($_POST['new_lname']  ?? '');
        $full   = trim($fname . ' ' . $lname);
        $pass   = $_POST['new_pass'] ?? '';
        $admin  = isset($_POST['new_admin']) ? 1 : 0;

        if ($uname === '' || $email === '' || $full === ' ' || $pass === '') {
            $err = 'All fields are required to create a user.';
        } else {
            // Check for duplicate username or email
            $chk = mysqli_prepare($conn, 'SELECT user_ID FROM `User` WHERE UName = ? OR email_address = ? LIMIT 1');
            mysqli_stmt_bind_param($chk, 'ss', $uname, $email);
            mysqli_stmt_execute($chk);
            mysqli_stmt_store_result($chk);
            if (mysqli_stmt_num_rows($chk) > 0) {
                $err = 'Username or email already exists.';
            } else {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $ins  = mysqli_prepare($conn, 'INSERT INTO `User` (full_name, administrator, email_address, friends, Upswd, UName) VALUES (?, ?, ?, NULL, ?, ?)');
                mysqli_stmt_bind_param($ins, 'sisss', $full, $admin, $email, $hash, $uname);
                mysqli_stmt_execute($ins);
                $newId = mysqli_insert_id($conn);
                mysqli_stmt_close($ins);
                // Create a friendlist for the new user
                $fl = mysqli_prepare($conn, 'INSERT INTO Friendlist (`user`) VALUES (?)');
                mysqli_stmt_bind_param($fl, 'i', $newId);
                mysqli_stmt_execute($fl);
                $flId = mysqli_insert_id($conn);
                mysqli_stmt_close($fl);
                $upd = mysqli_prepare($conn, 'UPDATE `User` SET friends = ? WHERE user_ID = ?');
                mysqli_stmt_bind_param($upd, 'ii', $flId, $newId);
                mysqli_stmt_execute($upd);
                mysqli_stmt_close($upd);
                $msg = 'User created successfully.';
            }
            mysqli_stmt_close($chk);
        }
    }

    // Edit an existing user
    elseif ($action === 'edit_user') {
        $editId = (int)($_POST['edit_user_id'] ?? 0);
        $uname  = trim($_POST['edit_uname']  ?? '');
        $email  = trim($_POST['edit_email']  ?? '');
        $fname  = trim($_POST['edit_fname']  ?? '');
        $lname  = trim($_POST['edit_lname']  ?? '');
        $full   = trim($fname . ' ' . $lname);
        $pass   = $_POST['edit_pass'] ?? '';
        $admin  = isset($_POST['edit_admin']) ? 1 : 0;

        if ($editId <= 0 || $uname === '' || $email === '') {
            $err = 'Invalid data submitted.';
        } else {
            // Update basic fields
            $upd = mysqli_prepare($conn, 'UPDATE `User` SET UName = ?, email_address = ?, full_name = ?, administrator = ? WHERE user_ID = ?');
            mysqli_stmt_bind_param($upd, 'sssii', $uname, $email, $full, $admin, $editId);
            mysqli_stmt_execute($upd);
            mysqli_stmt_close($upd);
            // Update password only if provided
            if ($pass !== '') {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $upd2 = mysqli_prepare($conn, 'UPDATE `User` SET Upswd = ? WHERE user_ID = ?');
                mysqli_stmt_bind_param($upd2, 'si', $hash, $editId);
                mysqli_stmt_execute($upd2);
                mysqli_stmt_close($upd2);
            }
            $msg = 'User updated.';
        }
    }

    // Delete a user (cannot delete yourself)
    elseif ($action === 'delete_user') {
        $delId = (int)($_POST['del_user_id'] ?? 0);
        if ($delId === $userId) {
            $err = 'You cannot delete your own account.';
        } elseif ($delId > 0) {
            $del = mysqli_prepare($conn, 'DELETE FROM `User` WHERE user_ID = ?');
            mysqli_stmt_bind_param($del, 'i', $delId);
            mysqli_stmt_execute($del);
            mysqli_stmt_close($del);
            $msg = 'User deleted.';
        }
    }

    // ── STATIONS ─────────────────────────────────────────────────────────────

    // Create a new station
    elseif ($action === 'create_station') {
        $serial = (int)($_POST['new_serial'] ?? 0);
        $sname  = trim($_POST['new_sname']  ?? '');
        $sdesc  = trim($_POST['new_sdesc']  ?? '');
        $suser  = $_POST['new_suser'] !== '' ? (int)$_POST['new_suser'] : null;

        if ($serial <= 0 || $sname === '') {
            $err = 'Serial number and name are required.';
        } else {
            // Check serial not already taken
            $chk = mysqli_prepare($conn, 'SELECT serial_number FROM Station WHERE serial_number = ? LIMIT 1');
            mysqli_stmt_bind_param($chk, 'i', $serial);
            mysqli_stmt_execute($chk);
            mysqli_stmt_store_result($chk);
            if (mysqli_stmt_num_rows($chk) > 0) {
                $err = 'A station with that serial number already exists.';
            } else {
                $ins = mysqli_prepare($conn, 'INSERT INTO Station (serial_number, station_name, station_description, user_station) VALUES (?, ?, ?, ?)');
                mysqli_stmt_bind_param($ins, 'issi', $serial, $sname, $sdesc, $suser);
                mysqli_stmt_execute($ins);
                mysqli_stmt_close($ins);
                $msg = 'Station created.';
            }
            mysqli_stmt_close($chk);
        }
    }

    // Edit a station
    elseif ($action === 'edit_station') {
        $serial = (int)($_POST['edit_serial'] ?? 0);
        $sname  = trim($_POST['edit_sname']  ?? '');
        $sdesc  = trim($_POST['edit_sdesc']  ?? '');
        $suser  = $_POST['edit_suser'] !== '' ? (int)$_POST['edit_suser'] : null;

        if ($serial <= 0 || $sname === '') {
            $err = 'Invalid data submitted.';
        } else {
            $upd = mysqli_prepare($conn, 'UPDATE Station SET station_name = ?, station_description = ?, user_station = ? WHERE serial_number = ?');
            mysqli_stmt_bind_param($upd, 'ssii', $sname, $sdesc, $suser, $serial);
            mysqli_stmt_execute($upd);
            mysqli_stmt_close($upd);
            $msg = 'Station updated.';
        }
    }

    // Delete a station
    elseif ($action === 'delete_station') {
        $serial = (int)($_POST['del_serial'] ?? 0);
        if ($serial > 0) {
            $del = mysqli_prepare($conn, 'DELETE FROM Station WHERE serial_number = ?');
            mysqli_stmt_bind_param($del, 'i', $serial);
            mysqli_stmt_execute($del);
            mysqli_stmt_close($del);
            $msg = 'Station deleted.';
        }
    }

    // ── MEASUREMENTS ─────────────────────────────────────────────────────────

    // Delete a single measurement
    elseif ($action === 'delete_measurement') {
        $mId = (int)($_POST['del_measurement_id'] ?? 0);
        if ($mId > 0) {
            $del = mysqli_prepare($conn, 'DELETE FROM Measurement WHERE measurement_ID = ?');
            mysqli_stmt_bind_param($del, 'i', $mId);
            mysqli_stmt_execute($del);
            mysqli_stmt_close($del);
            $msg = 'Measurement deleted.';
        }
    }

    // ── COLLECTIONS ──────────────────────────────────────────────────────────

    // Rename any collection
    elseif ($action === 'rename_collection') {
        $cId   = (int)($_POST['ren_col_id'] ?? 0);
        $cName = trim($_POST['ren_col_name'] ?? '');
        if ($cId > 0 && $cName !== '') {
            $upd = mysqli_prepare($conn, 'UPDATE Collection SET collection_name = ? WHERE collection_ID = ?');
            mysqli_stmt_bind_param($upd, 'si', $cName, $cId);
            mysqli_stmt_execute($upd);
            mysqli_stmt_close($upd);
            $msg = 'Collection renamed.';
        }
    }

    // Delete any collection
    elseif ($action === 'delete_collection') {
        $cId = (int)($_POST['del_col_id'] ?? 0);
        if ($cId > 0) {
            foreach (['DELETE FROM Collection_Measurements WHERE collection_ID = ?',
                      'DELETE FROM User_Collections WHERE collection_ID = ?',
                      'DELETE FROM Collection WHERE collection_ID = ?'] as $sql) {
                $s = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($s, 'i', $cId);
                mysqli_stmt_execute($s);
                mysqli_stmt_close($s);
            }
            $msg = 'Collection deleted.';
        }
    }
}

// ─── LOAD DATA FOR DISPLAY ───────────────────────────────────────────────────

// All users
$allUsers = [];
$r = mysqli_query($conn, 'SELECT user_ID, UName, full_name, email_address, administrator FROM `User` ORDER BY UName');
while ($row = mysqli_fetch_assoc($r)) { $allUsers[] = $row; }

// All stations (with optional owner name)
$allStations = [];
$r = mysqli_query($conn, 'SELECT s.serial_number, s.station_name, s.station_description, s.user_station, u.UName
                           FROM Station s LEFT JOIN `User` u ON s.user_station = u.user_ID
                           ORDER BY s.serial_number');
while ($row = mysqli_fetch_assoc($r)) { $allStations[] = $row; }

// All collections (with owner name)
$allCollections = [];
$r = mysqli_query($conn, 'SELECT c.collection_ID, c.collection_name, u.UName
                           FROM Collection c
                           LEFT JOIN User_Collections uc ON c.collection_ID = uc.collection_ID
                           LEFT JOIN `User` u ON uc.user = u.user_ID
                           ORDER BY c.collection_name');
while ($row = mysqli_fetch_assoc($r)) { $allCollections[] = $row; }

// Measurement filter values from GET
$filterStation   = (int)($_GET['f_station']    ?? 0);
$filterStartDate = $_GET['f_start_date'] ?? '';
$filterStartTime = $_GET['f_start_time'] ?? '00:00';
$filterEndDate   = $_GET['f_end_date']   ?? '';
$filterEndTime   = $_GET['f_end_time']   ?? '23:59';
$measurements    = [];
if ($filterStation > 0 && $filterStartDate !== '' && $filterEndDate !== '') {
    $startDt = $filterStartDate . ' ' . $filterStartTime . ':00';
    $endDt   = $filterEndDate   . ' ' . $filterEndTime   . ':59';
    $mStmt   = mysqli_prepare($conn,
        'SELECT m.measurement_ID, m.timestamp_Measurement, m.temperature, m.humidity, m.airpressure, m.lightintensity, m.airquality, s.station_name
         FROM Measurement m JOIN Station s ON m.station = s.serial_number
         WHERE m.station = ? AND m.timestamp_Measurement BETWEEN ? AND ?
         ORDER BY m.timestamp_Measurement DESC');
    mysqli_stmt_bind_param($mStmt, 'iss', $filterStation, $startDt, $endDt);
    mysqli_stmt_execute($mStmt);
    $mRes = mysqli_stmt_get_result($mStmt);
    while ($row = mysqli_fetch_assoc($mRes)) { $measurements[] = $row; }
    mysqli_stmt_close($mStmt);
}
?>

<div class="container">
    <h1 class="Title">Admin Panel</h1>
    <p class="lead">Manage users, stations, measurements, and collections.</p>

    <?php if ($msg !== ''): ?>
        <div class="alert" style="background:#d1fae5;border-color:#6ee7b7;color:#065f46;"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if ($err !== ''): ?>
        <div class="alert" style="background:#fee2e2;border-color:#fca5a5;color:#991b1b;"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <!-- Tab buttons -->
    <div class="admin-tabs">
        <button class="admin-tab active" onclick="showTab('users')">Users</button>
        <button class="admin-tab" onclick="showTab('stations')">Stations</button>
        <button class="admin-tab" onclick="showTab('measurements')">Measurements</button>
        <button class="admin-tab" onclick="showTab('collections')">Collections</button>
    </div>

    <!-- ── USERS TAB ─────────────────────────────────────────────────────── -->
    <div id="tab-users" class="admin-section active">

        <!-- Create user form -->
        <div class="section-card">
            <h2>Create New User</h2>
            <form method="post">
                <input type="hidden" name="action" value="create_user">
                <div class="form-grid">
                    <div><label>First Name</label><input type="text" name="new_fname" required></div>
                    <div><label>Last Name</label><input type="text" name="new_lname" required></div>
                    <div><label>Username</label><input type="text" name="new_uname" required></div>
                    <div><label>Email</label><input type="email" name="new_email" required></div>
                    <div><label>Password</label><input type="password" name="new_pass" required></div>
                    <div style="display:flex;align-items:flex-end;gap:0.5rem;padding-bottom:0.1rem;">
                        <label style="margin:0;"><input type="checkbox" name="new_admin" value="1"> Administrator</label>
                    </div>
                </div>
                <div class="button-row"><button type="submit">Create User</button></div>
            </form>
        </div>

        <!-- All users table -->
        <div class="section-card">
            <h2>All Users</h2>
            <table>
                <tr><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Admin</th><th>Actions</th></tr>
                <?php foreach ($allUsers as $u): ?>
                <tr>
                    <td><?= (int)$u['user_ID'] ?></td>
                    <td><?= htmlspecialchars($u['UName'],       ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($u['full_name'],   ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($u['email_address'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= $u['administrator'] ? 'Yes' : 'No' ?></td>
                    <td style="display:flex;gap:0.4rem;flex-wrap:wrap;">
                        <!-- Edit button opens inline form -->
                        <button class="btn-sm" onclick="toggleEdit('user-<?= (int)$u['user_ID'] ?>')">Edit</button>
                        <?php if ((int)$u['user_ID'] !== $userId): ?>
                            <form method="post" onsubmit="return confirm('Delete this user?');" style="margin:0;">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="del_user_id" value="<?= (int)$u['user_ID'] ?>">
                                <button type="submit" class="btn-danger btn-sm">Delete</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <!-- Inline edit row (hidden by default) -->
                <tr id="user-<?= (int)$u['user_ID'] ?>" style="display:none;background:#eff6ff;">
                    <td colspan="6">
                        <form method="post" style="padding:0.5rem 0;">
                            <input type="hidden" name="action" value="edit_user">
                            <input type="hidden" name="edit_user_id" value="<?= (int)$u['user_ID'] ?>">
                            <?php
                                $parts = explode(' ', $u['full_name'] ?? '', 2);
                                $efn   = $parts[0] ?? '';
                                $eln   = $parts[1] ?? '';
                            ?>
                            <div class="form-grid">
                                <div><label>First Name</label><input type="text" name="edit_fname" value="<?= htmlspecialchars($efn, ENT_QUOTES, 'UTF-8') ?>" required></div>
                                <div><label>Last Name</label><input type="text" name="edit_lname" value="<?= htmlspecialchars($eln, ENT_QUOTES, 'UTF-8') ?>" required></div>
                                <div><label>Username</label><input type="text" name="edit_uname" value="<?= htmlspecialchars($u['UName'], ENT_QUOTES, 'UTF-8') ?>" required></div>
                                <div><label>Email</label><input type="email" name="edit_email" value="<?= htmlspecialchars($u['email_address'], ENT_QUOTES, 'UTF-8') ?>" required></div>
                                <div><label>New Password (blank = keep)</label><input type="password" name="edit_pass"></div>
                                <div style="display:flex;align-items:flex-end;">
                                    <label><input type="checkbox" name="edit_admin" value="1" <?= $u['administrator'] ? 'checked' : '' ?>> Administrator</label>
                                </div>
                            </div>
                            <div class="button-row"><button type="submit">Save Changes</button></div>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <!-- ── STATIONS TAB ──────────────────────────────────────────────────── -->
    <div id="tab-stations" class="admin-section">

        <!-- Create station form -->
        <div class="section-card">
            <h2>Create New Station</h2>
            <form method="post">
                <input type="hidden" name="action" value="create_station">
                <div class="form-grid">
                    <div><label>Serial Number</label><input type="number" name="new_serial" required></div>
                    <div><label>Station Name</label><input type="text" name="new_sname" required></div>
                    <div><label>Description</label><input type="text" name="new_sdesc"></div>
                    <div><label>Assign to User (optional)</label>
                        <select name="new_suser">
                            <option value="">— Unassigned —</option>
                            <?php foreach ($allUsers as $u): ?>
                                <option value="<?= (int)$u['user_ID'] ?>"><?= htmlspecialchars($u['UName'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="button-row"><button type="submit">Create Station</button></div>
            </form>
        </div>

        <!-- All stations table -->
        <div class="section-card">
            <h2>All Stations</h2>
            <table>
                <tr><th>Serial</th><th>Name</th><th>Description</th><th>Owner</th><th>Actions</th></tr>
                <?php foreach ($allStations as $s): ?>
                <tr>
                    <td><?= (int)$s['serial_number'] ?></td>
                    <td><?= htmlspecialchars($s['station_name'],        ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($s['station_description'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= $s['UName'] ? htmlspecialchars($s['UName'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
                    <td style="display:flex;gap:0.4rem;flex-wrap:wrap;">
                        <button class="btn-sm" onclick="toggleEdit('station-<?= (int)$s['serial_number'] ?>')">Edit</button>
                        <form method="post" onsubmit="return confirm('Delete this station and all its measurements?');" style="margin:0;">
                            <input type="hidden" name="action" value="delete_station">
                            <input type="hidden" name="del_serial" value="<?= (int)$s['serial_number'] ?>">
                            <button type="submit" class="btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
                <!-- Inline edit row -->
                <tr id="station-<?= (int)$s['serial_number'] ?>" style="display:none;background:#eff6ff;">
                    <td colspan="5">
                        <form method="post" style="padding:0.5rem 0;">
                            <input type="hidden" name="action" value="edit_station">
                            <input type="hidden" name="edit_serial" value="<?= (int)$s['serial_number'] ?>">
                            <div class="form-grid">
                                <div><label>Station Name</label><input type="text" name="edit_sname" value="<?= htmlspecialchars($s['station_name'], ENT_QUOTES, 'UTF-8') ?>" required></div>
                                <div><label>Description</label><input type="text" name="edit_sdesc" value="<?= htmlspecialchars($s['station_description'], ENT_QUOTES, 'UTF-8') ?>"></div>
                                <div><label>Assign to User</label>
                                    <select name="edit_suser">
                                        <option value="">— Unassigned —</option>
                                        <?php foreach ($allUsers as $u): ?>
                                            <option value="<?= (int)$u['user_ID'] ?>" <?= (int)$s['user_station'] === (int)$u['user_ID'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($u['UName'], ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="button-row"><button type="submit">Save Changes</button></div>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <!-- ── MEASUREMENTS TAB ──────────────────────────────────────────────── -->
    <div id="tab-measurements" class="admin-section">
        <div class="section-card">
            <h2>Filter Measurements</h2>
            <form method="get">
                <input type="hidden" name="tab" value="measurements">
                <div class="form-grid">
                    <div><label>Station</label>
                        <select name="f_station" required>
                            <option value="">Select a station</option>
                            <?php foreach ($allStations as $s): ?>
                                <option value="<?= (int)$s['serial_number'] ?>" <?= $filterStation === (int)$s['serial_number'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['station_name'], ENT_QUOTES, 'UTF-8') ?> (#<?= (int)$s['serial_number'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div></div>
                    <div><label>Start Date</label><input type="date" name="f_start_date" value="<?= htmlspecialchars($filterStartDate, ENT_QUOTES, 'UTF-8') ?>" required>
                         <input type="time" name="f_start_time" value="<?= htmlspecialchars($filterStartTime, ENT_QUOTES, 'UTF-8') ?>" required></div>
                    <div><label>End Date</label><input type="date" name="f_end_date" value="<?= htmlspecialchars($filterEndDate, ENT_QUOTES, 'UTF-8') ?>" required>
                         <input type="time" name="f_end_time" value="<?= htmlspecialchars($filterEndTime, ENT_QUOTES, 'UTF-8') ?>" required></div>
                </div>
                <div class="button-row"><button type="submit">Show Measurements</button></div>
            </form>
        </div>

        <?php if ($filterStation > 0 && $filterStartDate !== '' && $filterEndDate !== ''): ?>
        <div class="section-card">
            <h2>Results</h2>
            <?php if (count($measurements) > 0): ?>
            <table>
                <tr><th>ID</th><th>Timestamp</th><th>Temp (°C)</th><th>Humidity (%)</th><th>Pressure</th><th>Light</th><th>Air Quality</th><th>Action</th></tr>
                <?php foreach ($measurements as $m): ?>
                <tr>
                    <td><?= (int)$m['measurement_ID'] ?></td>
                    <td><?= htmlspecialchars($m['timestamp_Measurement'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($m['temperature'],  ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($m['humidity'],     ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($m['airpressure'],  ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($m['lightintensity'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($m['airquality'],   ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <form method="post" onsubmit="return confirm('Delete this measurement?');" style="margin:0;">
                            <input type="hidden" name="action" value="delete_measurement">
                            <input type="hidden" name="del_measurement_id" value="<?= (int)$m['measurement_ID'] ?>">
                            <button type="submit" class="btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php else: ?>
                <div class="alert">No measurements found for this station in the selected range.</div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── COLLECTIONS TAB ───────────────────────────────────────────────── -->
    <div id="tab-collections" class="admin-section">
        <div class="section-card">
            <h2>All Collections</h2>
            <?php if (count($allCollections) > 0): ?>
            <table>
                <tr><th>ID</th><th>Name</th><th>Owner</th><th>Actions</th></tr>
                <?php foreach ($allCollections as $c): ?>
                <tr>
                    <td><?= (int)$c['collection_ID'] ?></td>
                    <td><?= htmlspecialchars($c['collection_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= $c['UName'] ? htmlspecialchars($c['UName'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
                    <td style="display:flex;gap:0.4rem;flex-wrap:wrap;">
                        <button class="btn-sm" onclick="toggleEdit('col-<?= (int)$c['collection_ID'] ?>')">Rename</button>
                        <form method="post" onsubmit="return confirm('Delete this collection?');" style="margin:0;">
                            <input type="hidden" name="action" value="delete_collection">
                            <input type="hidden" name="del_col_id" value="<?= (int)$c['collection_ID'] ?>">
                            <button type="submit" class="btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
                <!-- Inline rename row -->
                <tr id="col-<?= (int)$c['collection_ID'] ?>" style="display:none;background:#eff6ff;">
                    <td colspan="4">
                        <form method="post" style="display:flex;gap:0.75rem;align-items:center;padding:0.4rem 0;">
                            <input type="hidden" name="action" value="rename_collection">
                            <input type="hidden" name="ren_col_id" value="<?= (int)$c['collection_ID'] ?>">
                            <input type="text" name="ren_col_name" value="<?= htmlspecialchars($c['collection_name'], ENT_QUOTES, 'UTF-8') ?>" required style="max-width:300px;">
                            <button type="submit" class="btn-sm">Save</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php else: ?>
                <div class="alert">No collections found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Switch between admin tabs
    function showTab(name) {
        document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
        document.querySelectorAll('.admin-tab').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-' + name).classList.add('active');
        event.target.classList.add('active');
    }

    // Toggle inline edit rows
    function toggleEdit(id) {
        const row = document.getElementById(id);
        if (row) row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
    }

    // Keep measurements tab active after a GET filter
    <?php if (isset($_GET['tab']) && $_GET['tab'] === 'measurements'): ?>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
        document.querySelectorAll('.admin-tab').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-measurements').classList.add('active');
        document.querySelectorAll('.admin-tab')[2].classList.add('active');
    });
    <?php endif; ?>
</script>

</body>
</html>
