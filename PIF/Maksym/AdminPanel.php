<?php
include 'CommonCode.php';
requireAdmin();

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // === USERS ===
    if ($action === 'create_user') {
        $u  = trim($_POST['username']  ?? '');
        $fn = trim($_POST['firstName'] ?? '');
        $ln = trim($_POST['lastName']  ?? '');
        $em = trim($_POST['email']     ?? '');
        $pw = $_POST['password']  ?? '';
        $r  = ($_POST['role'] ?? 'User') === 'Admin' ? 'Admin' : 'User';

        if ($u === '' || $fn === '' || $ln === '' || $em === '' || $pw === '') {
            $error_message = 'All fields are required.';
        } elseif (!filter_var($em, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Invalid email.';
        } elseif (userExists($u)) {
            $error_message = 'Username already exists.';
        } elseif (emailExists($em)) {
            $error_message = 'Email already exists.';
        } else {
            $hash = password_hash($pw, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO user (pk_username, firstName, lastName, password, email, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $u, $fn, $ln, $hash, $em, $r);
            if ($stmt->execute()) $success_message = 'User created.';
            else                  $error_message   = 'Database error creating user.';
            $stmt->close();
        }
    }

    if ($action === 'edit_user') {
        $u  = $_POST['username'] ?? '';
        $fn = trim($_POST['firstName'] ?? '');
        $ln = trim($_POST['lastName']  ?? '');
        $em = trim($_POST['email']     ?? '');
        $r  = ($_POST['role'] ?? 'User') === 'Admin' ? 'Admin' : 'User';
        $pw = $_POST['password'] ?? '';

        if ($pw !== '') {
            $hash = password_hash($pw, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE user SET firstName=?, lastName=?, email=?, role=?, password=? WHERE pk_username=?");
            $stmt->bind_param("ssssss", $fn, $ln, $em, $r, $hash, $u);
        } else {
            $stmt = $conn->prepare("UPDATE user SET firstName=?, lastName=?, email=?, role=? WHERE pk_username=?");
            $stmt->bind_param("sssss", $fn, $ln, $em, $r, $u);
        }
        if ($stmt->execute()) $success_message = 'User updated.';
        else                  $error_message   = 'Database error updating user.';
        $stmt->close();
    }

    if ($action === 'delete_user') {
        $u = $_POST['username'] ?? '';
        if ($u === $_SESSION['username']) {
            $error_message = "You can't delete yourself.";
        } else {
            $stmt = $conn->prepare("DELETE FROM user WHERE pk_username = ?");
            $stmt->bind_param("s", $u);
            if ($stmt->execute()) $success_message = 'User deleted.';
            else                  $error_message   = 'Database error deleting user.';
            $stmt->close();
        }
    }

    // === STATIONS ===
    if ($action === 'create_station') {
        $sn = trim($_POST['serial']      ?? '');
        $n  = trim($_POST['name']        ?? '');
        $d  = trim($_POST['description'] ?? '');
        $o  = trim($_POST['owner']       ?? '');
        $owner = $o !== '' ? $o : null;

        if ($sn === '') {
            $error_message = 'Serial is required.';
        } else {
            $st = getStationBySerial($sn);
            if ($st) {
                $error_message = 'Serial already exists.';
            } else {
                $stmt = $conn->prepare("INSERT INTO station (pk_serialNumber, name, description, fk_user_owns) VALUES (?, ?, ?, ?)");
                $name_or_null = $n !== '' ? $n : null;
                $desc_or_null = $d !== '' ? $d : null;
                $stmt->bind_param("ssss", $sn, $name_or_null, $desc_or_null, $owner);
                if ($stmt->execute()) $success_message = 'Station created.';
                else                  $error_message   = 'Database error creating station.';
                $stmt->close();
            }
        }
    }

    if ($action === 'edit_station') {
        $sn = $_POST['serial']           ?? '';
        $n  = trim($_POST['name']        ?? '');
        $d  = trim($_POST['description'] ?? '');
        $o  = trim($_POST['owner']       ?? '');
        $owner = $o !== '' ? $o : null;
        $name_or_null = $n !== '' ? $n : null;
        $desc_or_null = $d !== '' ? $d : null;

        $stmt = $conn->prepare("UPDATE station SET name=?, description=?, fk_user_owns=? WHERE pk_serialNumber=?");
        $stmt->bind_param("ssss", $name_or_null, $desc_or_null, $owner, $sn);
        if ($stmt->execute()) $success_message = 'Station updated.';
        else                  $error_message   = 'Database error updating station.';
        $stmt->close();
    }

    if ($action === 'delete_station') {
        $sn = $_POST['serial'] ?? '';
        $stmt = $conn->prepare("DELETE FROM station WHERE pk_serialNumber = ?");
        $stmt->bind_param("s", $sn);
        if ($stmt->execute()) $success_message = 'Station deleted.';
        else                  $error_message   = 'Database error deleting station.';
        $stmt->close();
    }

    if ($action === 'delete_measurement') {
        $mid = (int)($_POST['id'] ?? 0);
        if ($mid > 0 && deleteMeasurement($mid, $_SESSION['username'], true)) {
            $success_message = 'Measurement deleted.';
        } else {
            $error_message = 'Could not delete measurement.';
        }
    }

    if ($action === 'delete_collection') {
        $cid = (int)($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM collection WHERE pk_collection = ?");
        $stmt->bind_param("i", $cid);
        if ($stmt->execute()) $success_message = 'Collection deleted.';
        else                  $error_message   = 'Database error deleting collection.';
        $stmt->close();
    }

    if ($action === 'rename_collection') {
        $cid = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        if ($cid > 0 && $name !== '') {
            $stmt = $conn->prepare("UPDATE collection SET name = ? WHERE pk_collection = ?");
            $stmt->bind_param("si", $name, $cid);
            if ($stmt->execute()) $success_message = 'Collection renamed.';
            else                  $error_message   = 'Database error renaming collection.';
            $stmt->close();
        }
    }
}

// Fetch lists
$users = [];
$uq = $conn->prepare("SELECT pk_username, firstName, lastName, email, role FROM user ORDER BY role DESC, pk_username");
if ($uq) { $uq->execute(); $ur = $uq->get_result(); while ($row = $ur->fetch_assoc()) $users[] = $row; $uq->close(); }

$stationsList = [];
$sq = $conn->prepare("SELECT s.pk_serialNumber, s.name, s.description, s.fk_user_owns, u.firstName, u.lastName FROM station s LEFT JOIN user u ON s.fk_user_owns = u.pk_username ORDER BY s.pk_serialNumber");
if ($sq) { $sq->execute(); $sr = $sq->get_result(); while ($row = $sr->fetch_assoc()) $stationsList[] = $row; $sq->close(); }

$selStation = $_GET['adm_station'] ?? '';
$adm_start  = $_GET['adm_start']   ?? date('Y-m-d 00:00:00', strtotime('-1 day'));
$adm_end    = $_GET['adm_end']     ?? date('Y-m-d 23:59:59');
$adminMeasurements = [];
if ($selStation !== '') {
    $mq = $conn->prepare("SELECT m.pk_measurement, m.fk_station_records, s.name AS station_name, m.timestamp, m.temperature, m.humidity, m.pressure, m.light, m.gas FROM measurement m LEFT JOIN station s ON m.fk_station_records = s.pk_serialNumber WHERE m.fk_station_records = ? AND m.timestamp BETWEEN ? AND ? ORDER BY m.timestamp DESC LIMIT 500");
    $mq->bind_param("sss", $selStation, $adm_start, $adm_end);
    $mq->execute();
    $mr = $mq->get_result();
    while ($row = $mr->fetch_assoc()) $adminMeasurements[] = $row;
    $mq->close();
}

$collectionsList = [];
$cq = $conn->prepare("SELECT c.pk_collection, c.name, c.description, c.fk_user_creates, u.firstName, u.lastName, (SELECT COUNT(*) FROM contains ct WHERE ct.pkfk_collection = c.pk_collection) AS measurement_count FROM collection c LEFT JOIN user u ON c.fk_user_creates = u.pk_username ORDER BY c.name");
if ($cq) { $cq->execute(); $cr = $cq->get_result(); while ($row = $cr->fetch_assoc()) $collectionsList[] = $row; $cq->close(); }

$totalMeas = 0;
$tq = $conn->query("SELECT COUNT(*) AS c FROM measurement");
if ($tq) { $row = $tq->fetch_assoc(); $totalMeas = (int)$row['c']; }
?>
<!DOCTYPE html>
<html lang="<?php echo getLang(); ?>" data-theme="<?php echo getTheme(); ?>">
<head>
  <meta charset="UTF-8" />
  <title>PIF - <?php echo t('admin'); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
  <?php NavigationBar('admin'); ?>

  <div class="container">
    <div class="page-title"><?php echo t('admin'); ?></div>
    <div class="page-sub"><?php echo t('admin_desc'); ?></div>

    <?php if ($success_message): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="stats" style="margin-bottom:1.5rem;">
      <div class="stat" style="--s-color:#00d4ff"><div class="stat-val"><?php echo count($users); ?></div><div class="stat-label"><?php echo t('all_users'); ?></div></div>
      <div class="stat" style="--s-color:#818cf8"><div class="stat-val"><?php echo count($stationsList); ?></div><div class="stat-label"><?php echo t('all_stations'); ?></div></div>
      <div class="stat" style="--s-color:#fbbf24"><div class="stat-val"><?php echo $totalMeas; ?></div><div class="stat-label"><?php echo t('measurements'); ?></div></div>
      <div class="stat" style="--s-color:#4ade80"><div class="stat-val"><?php echo count($collectionsList); ?></div><div class="stat-label"><?php echo t('collections'); ?></div></div>
    </div>

    <div class="card">
      <div class="tabs">
        <div id="tabUsersBtn" class="tab-btn active"><?php echo t('all_users'); ?></div>
        <div id="tabStationsBtn" class="tab-btn"><?php echo t('all_stations'); ?></div>
        <div id="tabMeasurementsBtn" class="tab-btn"><?php echo t('measurements'); ?></div>
        <div id="tabCollectionsBtn" class="tab-btn"><?php echo t('all_collections'); ?></div>
      </div>

      <div id="panelUsers">
        <h2><?php echo t('create_user'); ?></h2>
        <form method="POST" style="margin-bottom:1.5rem;">
          <input type="hidden" name="action" value="create_user" />
          <div class="form-row">
            <input type="text" name="username" placeholder="<?php echo t('username'); ?>" required />
            <input type="text" name="firstName" placeholder="<?php echo t('first_name'); ?>" required />
            <input type="text" name="lastName" placeholder="<?php echo t('last_name'); ?>" required />
          </div>
          <div class="form-row">
            <input type="email" name="email" placeholder="<?php echo t('email'); ?>" required />
            <input type="password" name="password" placeholder="<?php echo t('password'); ?>" required />
            <select name="role">
              <option value="User">User</option>
              <option value="Admin">Admin</option>
            </select>
            <button type="submit"><?php echo t('create_user'); ?></button>
          </div>
        </form>

        <h2><?php echo t('all_users'); ?></h2>
        <div class="table-wrap">
          <table>
            <thead><tr><th><?php echo t('username'); ?></th><th><?php echo t('name'); ?></th><th><?php echo t('email'); ?></th><th><?php echo t('role'); ?></th><th><?php echo t('actions'); ?></th></tr></thead>
            <tbody>
              <?php if (count($users) === 0): ?>
                <tr><td colspan="5" class="empty">No users found.</td></tr>
              <?php else: ?>
                <?php foreach ($users as $u): ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($u['pk_username']); ?></strong></td>
                    <td><?php echo htmlspecialchars($u['firstName'] . ' ' . $u['lastName']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><span class="badge badge-<?php echo strtolower($u['role']); ?>"><?php echo htmlspecialchars($u['role']); ?></span></td>
                    <td>
                      <button type="button" class="btn-xs" onclick="toggleRow('ue-<?php echo htmlspecialchars($u['pk_username']); ?>')"><?php echo t('edit'); ?></button>
                      <?php if ($u['pk_username'] !== $_SESSION['username']): ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user?');">
                          <input type="hidden" name="action" value="delete_user" />
                          <input type="hidden" name="username" value="<?php echo htmlspecialchars($u['pk_username']); ?>" />
                          <button type="submit" class="danger btn-xs"><?php echo t('delete'); ?></button>
                        </form>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr id="ue-<?php echo htmlspecialchars($u['pk_username']); ?>" style="display:none;">
                    <td colspan="5">
                      <form method="POST" style="padding:.75rem;">
                        <input type="hidden" name="action" value="edit_user" />
                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($u['pk_username']); ?>" />
                        <div class="form-row">
                          <input type="text" name="firstName" value="<?php echo htmlspecialchars($u['firstName']); ?>" required />
                          <input type="text" name="lastName" value="<?php echo htmlspecialchars($u['lastName']); ?>" required />
                          <input type="email" name="email" value="<?php echo htmlspecialchars($u['email']); ?>" required />
                          <select name="role">
                            <option value="User"  <?php if ($u['role'] === 'User')  echo 'selected'; ?>>User</option>
                            <option value="Admin" <?php if ($u['role'] === 'Admin') echo 'selected'; ?>>Admin</option>
                          </select>
                          <input type="password" name="password" placeholder="<?php echo t('leave_pwd_blank'); ?>" />
                          <button type="submit" class="btn-sm"><?php echo t('save'); ?></button>
                        </div>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div id="panelStations" class="hidden">
        <h2><?php echo t('add_station'); ?></h2>
        <form method="POST" style="margin-bottom:1.5rem;">
          <input type="hidden" name="action" value="create_station" />
          <div class="form-row">
            <input type="text" name="serial" placeholder="<?php echo t('serial_number'); ?> (SN-1001)" required />
            <input type="text" name="name" placeholder="<?php echo t('name'); ?> (<?php echo t('optional'); ?>)" />
            <select name="owner">
              <option value="">-- <?php echo t('unassigned'); ?> --</option>
              <?php foreach ($users as $u): ?>
                <option value="<?php echo htmlspecialchars($u['pk_username']); ?>"><?php echo htmlspecialchars($u['pk_username']); ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit"><?php echo t('add_station'); ?></button>
          </div>
          <div class="form-row">
            <input type="text" name="description" placeholder="<?php echo t('description'); ?> (<?php echo t('optional'); ?>)" style="flex:1;" />
          </div>
        </form>

        <h2><?php echo t('all_stations'); ?></h2>
        <div class="table-wrap">
          <table>
            <thead><tr><th><?php echo t('serial_number'); ?></th><th><?php echo t('name'); ?></th><th><?php echo t('owner'); ?></th><th><?php echo t('description'); ?></th><th><?php echo t('actions'); ?></th></tr></thead>
            <tbody>
              <?php if (count($stationsList) === 0): ?>
                <tr><td colspan="5" class="empty">No stations found.</td></tr>
              <?php else: ?>
                <?php foreach ($stationsList as $s): ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($s['pk_serialNumber']); ?></strong></td>
                    <td><?php echo htmlspecialchars($s['name'] ?? '-'); ?></td>
                    <td><?php echo $s['fk_user_owns'] ? htmlspecialchars($s['fk_user_owns']) : '<span style="color:var(--muted);">'.t('unassigned').'</span>'; ?></td>
                    <td><?php echo htmlspecialchars($s['description'] ?? ''); ?></td>
                    <td>
                      <button type="button" class="btn-xs" onclick="toggleRow('se-<?php echo htmlspecialchars($s['pk_serialNumber']); ?>')"><?php echo t('edit'); ?></button>
                      <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this station and all its data?');">
                        <input type="hidden" name="action" value="delete_station" />
                        <input type="hidden" name="serial" value="<?php echo htmlspecialchars($s['pk_serialNumber']); ?>" />
                        <button type="submit" class="danger btn-xs"><?php echo t('delete'); ?></button>
                      </form>
                    </td>
                  </tr>
                  <tr id="se-<?php echo htmlspecialchars($s['pk_serialNumber']); ?>" style="display:none;">
                    <td colspan="5">
                      <form method="POST" style="padding:.75rem;">
                        <input type="hidden" name="action" value="edit_station" />
                        <input type="hidden" name="serial" value="<?php echo htmlspecialchars($s['pk_serialNumber']); ?>" />
                        <div class="form-row">
                          <input type="text" name="name" placeholder="<?php echo t('name'); ?>" value="<?php echo htmlspecialchars($s['name'] ?? ''); ?>" />
                          <input type="text" name="description" placeholder="<?php echo t('description'); ?>" value="<?php echo htmlspecialchars($s['description'] ?? ''); ?>" />
                          <select name="owner">
                            <option value="">-- <?php echo t('unassigned'); ?> --</option>
                            <?php foreach ($users as $u): ?>
                              <option value="<?php echo htmlspecialchars($u['pk_username']); ?>" <?php if ($s['fk_user_owns'] === $u['pk_username']) echo 'selected'; ?>><?php echo htmlspecialchars($u['pk_username']); ?></option>
                            <?php endforeach; ?>
                          </select>
                          <button type="submit" class="btn-sm"><?php echo t('save'); ?></button>
                        </div>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div id="panelMeasurements" class="hidden">
        <form method="GET" style="margin-bottom:1.5rem;">
          <div class="form-row">
            <label><?php echo t('station'); ?>:</label>
            <select name="adm_station">
              <option value="">-- <?php echo t('station'); ?> --</option>
              <?php foreach ($stationsList as $st): ?>
                <option value="<?php echo htmlspecialchars($st['pk_serialNumber']); ?>" <?php if ($st['pk_serialNumber'] === $selStation) echo 'selected'; ?>><?php echo htmlspecialchars($st['pk_serialNumber'] . ($st['name'] ? ' - ' . $st['name'] : '')); ?></option>
              <?php endforeach; ?>
            </select>
            <label><?php echo t('from'); ?>:</label>
            <input type="datetime-local" name="adm_start" value="<?php echo htmlspecialchars(str_replace(' ', 'T', substr($adm_start, 0, 16))); ?>" />
            <label><?php echo t('to'); ?>:</label>
            <input type="datetime-local" name="adm_end" value="<?php echo htmlspecialchars(str_replace(' ', 'T', substr($adm_end, 0, 16))); ?>" />
            <button type="submit"><?php echo t('show'); ?></button>
          </div>
        </form>

        <?php if ($selStation === ''): ?>
          <div class="empty">Select a station to view measurements.</div>
        <?php elseif (count($adminMeasurements) === 0): ?>
          <div class="empty"><?php echo t('no_measurements'); ?></div>
        <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead><tr><th>ID</th><th><?php echo t('station'); ?></th><th>Timestamp</th><th>Temp</th><th>Humid.</th><th>Pres.</th><th>Light</th><th>Gas</th><th><?php echo t('actions'); ?></th></tr></thead>
              <tbody>
                <?php foreach ($adminMeasurements as $m): ?>
                  <tr>
                    <td style="color:var(--muted);"><?php echo (int)$m['pk_measurement']; ?></td>
                    <td><?php echo htmlspecialchars(($m['station_name'] ?? '') ?: $m['fk_station_records']); ?></td>
                    <td><?php echo htmlspecialchars($m['timestamp']); ?></td>
                    <td><?php echo number_format((float)$m['temperature'], 2); ?></td>
                    <td><?php echo number_format((float)$m['humidity'], 2); ?></td>
                    <td><?php echo number_format((float)$m['pressure'], 2); ?></td>
                    <td><?php echo number_format((float)$m['light'], 2); ?></td>
                    <td><?php echo number_format((float)$m['gas'], 2); ?></td>
                    <td>
                      <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this measurement?');">
                        <input type="hidden" name="action" value="delete_measurement" />
                        <input type="hidden" name="id" value="<?php echo (int)$m['pk_measurement']; ?>" />
                        <button type="submit" class="danger btn-xs"><?php echo t('delete'); ?></button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <div id="panelCollections" class="hidden">
        <h2><?php echo t('all_collections'); ?></h2>
        <div class="table-wrap">
          <table>
            <thead><tr><th>ID</th><th><?php echo t('name'); ?></th><th><?php echo t('creator'); ?></th><th><?php echo t('measurements'); ?></th><th><?php echo t('actions'); ?></th></tr></thead>
            <tbody>
              <?php if (count($collectionsList) === 0): ?>
                <tr><td colspan="5" class="empty">No collections found.</td></tr>
              <?php else: ?>
                <?php foreach ($collectionsList as $c): ?>
                  <tr>
                    <td style="color:var(--muted);"><?php echo (int)$c['pk_collection']; ?></td>
                    <td><strong><?php echo htmlspecialchars($c['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($c['fk_user_creates']); ?></td>
                    <td><?php echo (int)$c['measurement_count']; ?></td>
                    <td>
                      <button type="button" class="btn-xs" onclick="toggleRow('ce-<?php echo (int)$c['pk_collection']; ?>')"><?php echo t('rename'); ?></button>
                      <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this collection?');">
                        <input type="hidden" name="action" value="delete_collection" />
                        <input type="hidden" name="id" value="<?php echo (int)$c['pk_collection']; ?>" />
                        <button type="submit" class="danger btn-xs"><?php echo t('delete'); ?></button>
                      </form>
                    </td>
                  </tr>
                  <tr id="ce-<?php echo (int)$c['pk_collection']; ?>" style="display:none;">
                    <td colspan="5">
                      <form method="POST" style="padding:.75rem; display:flex; gap:.5rem;">
                        <input type="hidden" name="action" value="rename_collection" />
                        <input type="hidden" name="id" value="<?php echo (int)$c['pk_collection']; ?>" />
                        <input type="text" name="name" value="<?php echo htmlspecialchars($c['name']); ?>" required style="flex:1;" />
                        <button type="submit" class="btn-sm"><?php echo t('save'); ?></button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>

  <script>
    function toggleRow(id) {
      var $el = $('#' + id);
      if ($el.length) $el.css('display', $el.css('display') === 'none' ? 'table-row' : 'none');
    }

    $(document).ready(function() {
      function activate(tab) {
        $('#tabUsersBtn, #tabStationsBtn, #tabMeasurementsBtn, #tabCollectionsBtn').removeClass('active');
        $('#panelUsers, #panelStations, #panelMeasurements, #panelCollections').addClass('hidden');
        $('#tab' + tab.charAt(0).toUpperCase() + tab.slice(1) + 'Btn').addClass('active');
        $('#panel' + tab.charAt(0).toUpperCase() + tab.slice(1)).removeClass('hidden');
      }
      $('#tabUsersBtn').on('click',        function() { activate('users'); });
      $('#tabStationsBtn').on('click',     function() { activate('stations'); });
      $('#tabMeasurementsBtn').on('click', function() { activate('measurements'); });
      $('#tabCollectionsBtn').on('click',  function() { activate('collections'); });
    });
  </script>
</body>
</html>
