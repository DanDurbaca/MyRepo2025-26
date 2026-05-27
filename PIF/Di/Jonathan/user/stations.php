<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stations - Indoor Climate</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/app.js"></script>
</head>
<body>
    <?php
    // Stations management: claim, edit, and provision tokens for user's stations
    $pageTitle = 'Stations';
    require_once '../config.php';
    require_once __DIR__ . '/../_header.php';
    require_once __DIR__ . '/../inc/csrf.php';

    // Check login
    if (!isset($_SESSION['username'])) {
        header('Location: ../login.php');
        exit;
    }
    $username = $_SESSION['username'];

    // Load friend list for station sharing UI
    $friends = [];
    $friendStmt = $pdo->prepare("SELECT u.pk_username FROM `user` u JOIN isfriend f ON u.pk_username = f.pkfk_user_friend WHERE f.pkfk_user_user = ? ORDER BY u.pk_username");
    $friendStmt->execute([$username]);
    $friends = $friendStmt->fetchAll(PDO::FETCH_COLUMN, 0);

    // Handle form submissions
    $message = '';
    $messageType = 'info';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Save edited station (owner editing name/description)
        if (isset($_POST['save_station'])) {
            if (!validate_csrf($_POST['csrf_token'] ?? '')) {
                $message = 'Invalid CSRF token.';
                $messageType = 'danger';
            } else {
                $edit_serial = trim($_POST['edit_serial'] ?? '');
                $edit_name = trim($_POST['edit_name'] ?? '');
                $edit_description = trim($_POST['edit_description'] ?? '');
                if ($edit_serial === '' || $edit_name === '') {
                    $message = 'Serial and name are required.';
                    $messageType = 'warning';
                } else {
                    // ensure current user owns the station
                    $chk = $pdo->prepare("SELECT 1 FROM station WHERE pk_serialNumber = ? AND fk_user_owns = ?");
                    $chk->execute([$edit_serial, $username]);
                    if (!$chk->fetch()) {
                        $message = 'Unauthorized to edit this station.';
                        $messageType = 'danger';
                    } else {
                        $upd = $pdo->prepare("UPDATE station SET name = ?, description = ? WHERE pk_serialNumber = ?");
                        $upd->execute([$edit_name, $edit_description, $edit_serial]);
                        $message = 'Station updated.';
                        $messageType = 'success';
                    }
                }
            }
        }

        // Claim by provision token (QR)
        if (isset($_POST['claim_with_provision'])) {
            if (!validate_csrf($_POST['csrf_token'] ?? '')) {
                $message = 'Invalid CSRF token.';
                $messageType = 'danger';
            } else {
                $prov_serial = trim($_POST['prov_serial'] ?? '');
                $prov_token = trim($_POST['prov_token'] ?? '');
                if ($prov_serial === '' || $prov_token === '') {
                    $message = 'Provide both serial and token.';
                    $messageType = 'warning';
                } else {
                    // verify provision record
                    $stmt = $pdo->prepare('SELECT id, expires_at FROM station_provision WHERE pkfk_station = ? AND token = ? LIMIT 1');
                    $stmt->execute([$prov_serial, hash('sha256', $prov_token)]);
                    $row = $stmt->fetch();
                    if (!$row) {
                        $message = 'Invalid provision token.';
                        $messageType = 'danger';
                    } elseif (new DateTimeImmutable($row['expires_at']) < new DateTimeImmutable('now')) {
                        $message = 'Provision token expired.';
                        $messageType = 'warning';
                    } else {
                        $upd = $pdo->prepare('UPDATE station SET fk_user_owns = ? WHERE pk_serialNumber = ? AND fk_user_owns IS NULL');
                        $upd->execute([$username, $prov_serial]);
                        if ($upd->rowCount() > 0) {
                            // remove provision row
                            $pdo->prepare('DELETE FROM station_provision WHERE id = ?')->execute([$row['id']]);
                            $message = 'Station claimed successfully via token!';
                            $messageType = 'success';
                        } else {
                            $message = 'Station could not be claimed (maybe already claimed).';
                            $messageType = 'warning';
                        }
                    }
                }
            }
        }

        // Claim station: prefer typed serial, otherwise dropdown
        if (isset($_POST['claim_station'])) {
            if (!validate_csrf($_POST['csrf_token'] ?? '')) {
                $message = 'Invalid CSRF token.';
                $messageType = 'danger';
            } else {
                $serial = trim($_POST['station_serial'] ?? '');
                if ($serial === '') $serial = trim($_POST['station_id'] ?? '');
                if ($serial === '') {
                    $message = 'No station specified.';
                    $messageType = 'warning';
                } else {
                    // basic serial validation
                    if (!preg_match('/^[A-Za-z0-9_\-]{1,64}$/', $serial)) {
                        $message = 'Invalid serial format.';
                        $messageType = 'danger';
                    } else {
                        $stmt = $pdo->prepare("UPDATE station SET fk_user_owns = ? WHERE pk_serialNumber = ? AND fk_user_owns IS NULL");
                        $stmt->execute([$username, $serial]);
                        if ($stmt->rowCount() > 0) {
                            $message = 'Station claimed successfully!';
                            $messageType = 'success';
                        } else {
                            $message = 'Station could not be claimed (maybe already claimed or not registered).';
                            $messageType = 'warning';
                        }
                    }
                }
            }
        }

        // Share a station with a friend
        if (isset($_POST['share_station'])) {
            if (!validate_csrf($_POST['csrf_token'] ?? '')) {
                $message = 'Invalid CSRF token.';
                $messageType = 'danger';
            } else {
                $share_serial = trim($_POST['share_serial'] ?? '');
                $share_friend = trim($_POST['share_friend'] ?? '');
                if ($share_serial === '' || $share_friend === '') {
                    $message = 'Select a station and friend to share with.';
                    $messageType = 'warning';
                } elseif (!preg_match('/^[A-Za-z0-9_\-]{1,64}$/', $share_serial)) {
                    $message = 'Invalid station serial format.';
                    $messageType = 'danger';
                } elseif (!preg_match('/^[A-Za-z0-9_\-]{3,64}$/', $share_friend)) {
                    $message = 'Invalid friend username.';
                    $messageType = 'danger';
                } else {
                    $own = $pdo->prepare('SELECT 1 FROM station WHERE pk_serialNumber = ? AND fk_user_owns = ?');
                    $own->execute([$share_serial, $username]);
                    if (!$own->fetchColumn()) {
                        $message = 'You can only share stations you own.';
                        $messageType = 'danger';
                    } else {
                        $fr = $pdo->prepare('SELECT 1 FROM isfriend WHERE pkfk_user_user = ? AND pkfk_user_friend = ?');
                        $fr->execute([$username, $share_friend]);
                        if (!$fr->fetchColumn()) {
                            $message = 'You can only share with friends.';
                            $messageType = 'danger';
                        } else {
                            $existing = $pdo->prepare('SELECT status FROM station_share WHERE station_serial = ? AND shared_with = ? LIMIT 1');
                            $existing->execute([$share_serial, $share_friend]);
                            $status = $existing->fetchColumn();
                            if ($status === 'accepted') {
                                $message = 'Station already shared with this friend.';
                                $messageType = 'info';
                            } elseif ($status === 'pending') {
                                $message = 'Share request is already pending.';
                                $messageType = 'info';
                            } else {
                                $ins = $pdo->prepare("INSERT INTO station_share (station_serial, owner_user, shared_with, status) VALUES (?, ?, ?, 'pending') ON DUPLICATE KEY UPDATE owner_user = VALUES(owner_user), status = 'pending'");
                                $ins->execute([$share_serial, $username, $share_friend]);
                                $message = 'Share request sent.';
                                $messageType = 'success';
                            }
                        }
                    }
                }
            }
        }

        // Accept a pending share (friend action)
        if (isset($_POST['accept_share'])) {
            if (!validate_csrf($_POST['csrf_token'] ?? '')) {
                $message = 'Invalid CSRF token.';
                $messageType = 'danger';
            } else {
                $share_id = intval($_POST['accept_share']);
                if ($share_id <= 0) {
                    $message = 'Invalid share request.';
                    $messageType = 'danger';
                } else {
                    $upd = $pdo->prepare("UPDATE station_share SET status = 'accepted' WHERE id = ? AND shared_with = ? AND status = 'pending'");
                    $upd->execute([$share_id, $username]);
                    if ($upd->rowCount() > 0) {
                        $message = 'Station share accepted.';
                        $messageType = 'success';
                    } else {
                        $message = 'Share request not found or already handled.';
                        $messageType = 'warning';
                    }
                }
            }
        }

        // Decline or remove a shared station (friend action)
        if (isset($_POST['decline_share'])) {
            if (!validate_csrf($_POST['csrf_token'] ?? '')) {
                $message = 'Invalid CSRF token.';
                $messageType = 'danger';
            } else {
                $share_id = intval($_POST['decline_share']);
                if ($share_id <= 0) {
                    $message = 'Invalid share request.';
                    $messageType = 'danger';
                } else {
                    $upd = $pdo->prepare("UPDATE station_share SET status = 'declined' WHERE id = ? AND shared_with = ? AND status <> 'declined'");
                    $upd->execute([$share_id, $username]);
                    if ($upd->rowCount() > 0) {
                        $message = 'Share removed.';
                        $messageType = 'success';
                    } else {
                        $message = 'Share request not found or already handled.';
                        $messageType = 'warning';
                    }
                }
            }
        }

        // Revoke a share (owner action)
        if (isset($_POST['revoke_share'])) {
            if (!validate_csrf($_POST['csrf_token'] ?? '')) {
                $message = 'Invalid CSRF token.';
                $messageType = 'danger';
            } else {
                $share_id = intval($_POST['revoke_share']);
                if ($share_id <= 0) {
                    $message = 'Invalid share request.';
                    $messageType = 'danger';
                } else {
                    $upd = $pdo->prepare("UPDATE station_share SET status = 'declined' WHERE id = ? AND owner_user = ? AND status <> 'declined'");
                    $upd->execute([$share_id, $username]);
                    if ($upd->rowCount() > 0) {
                        $message = 'Share revoked.';
                        $messageType = 'success';
                    } else {
                        $message = 'Share request not found or already handled.';
                        $messageType = 'warning';
                    }
                }
            }
        }
    }
    ?>
    <div class="container">
        <h1>Your Stations</h1>
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <h2>Owned Stations</h2>
        <?php
        // List user's owned stations
        $stmt = $pdo->prepare("SELECT pk_serialNumber, name, description FROM station WHERE fk_user_owns = ?");
        $stmt->execute([$username]);
        $stations = $stmt->fetchAll();
        if (count($stations) > 0):
        ?>
        <table>
            <thead>
                <tr>
                    <th>Serial</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stations as $station):
                    $sn = htmlspecialchars($station['pk_serialNumber']);
                    $n = htmlspecialchars($station['name']);
                    $d = htmlspecialchars($station['description']);
                ?>
                <tr>
                    <td><?php echo $sn; ?></td>
                    <td><?php echo $n; ?></td>
                    <td><?php echo $d ?: '-'; ?></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary" onclick="editStation('<?php echo $sn; ?>', '<?php echo addslashes($n); ?>', '<?php echo addslashes($d); ?>')">Edit</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-muted">You don't have any stations yet. Claim one below.</p>
        <?php endif; ?>

        <!-- Edit Station Form (hidden by default) -->
        <div id="editForm" class="box" style="display: none; max-width: 400px;">
            <div class="box-header">Edit Station</div>
            <form method="post">
                <?php echo csrf_input(); ?>
                <input type="hidden" name="edit_serial" id="edit_serial">
                <div class="form-group">
                    <label for="edit_name">Name</label>
                    <input type="text" id="edit_name" name="edit_name" required>
                </div>
                <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="edit_description" rows="2"></textarea>
                </div>
                <button type="submit" name="save_station" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" onclick="$('#editForm').hide()">Cancel</button>
            </form>
        </div>

        <h2>Share a Station</h2>
        <?php if (count($stations) > 0 && count($friends) > 0): ?>
            <div class="box" style="max-width: 600px;">
                <div class="box-header">Share with a Friend</div>
                <form method="post">
                    <?php echo csrf_input(); ?>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="share_serial">Station</label>
                                <select id="share_serial" name="share_serial" required>
                                    <option value="">Select a station</option>
                                    <?php foreach ($stations as $station): ?>
                                        <option value="<?php echo htmlspecialchars($station['pk_serialNumber']); ?>">
                                            <?php echo htmlspecialchars($station['name'] . ' (' . $station['pk_serialNumber'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="share_friend">Friend</label>
                                <select id="share_friend" name="share_friend" required>
                                    <option value="">Select a friend</option>
                                    <?php foreach ($friends as $friend): ?>
                                        <option value="<?php echo htmlspecialchars($friend); ?>"><?php echo htmlspecialchars($friend); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="share_station" class="btn btn-primary">Send Share Request</button>
                </form>
            </div>
        <?php elseif (count($stations) === 0): ?>
            <p class="text-muted">You need to own a station before you can share it.</p>
        <?php else: ?>
            <p class="text-muted">Add friends first to share your stations.</p>
        <?php endif; ?>

        <h3>Incoming Station Shares</h3>
        <?php
        $incomingStmt = $pdo->prepare("SELECT ss.id, ss.station_serial, ss.owner_user, s.name FROM station_share ss JOIN station s ON s.pk_serialNumber = ss.station_serial WHERE ss.shared_with = ? AND ss.status = 'pending' ORDER BY ss.created_at DESC");
        $incomingStmt->execute([$username]);
        $incoming = $incomingStmt->fetchAll();
        if (count($incoming) > 0):
        ?>
        <table>
            <thead>
                <tr>
                    <th>Station</th>
                    <th>Shared By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($incoming as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name'] . ' (' . $row['station_serial'] . ')'); ?></td>
                    <td><?php echo htmlspecialchars($row['owner_user']); ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <?php echo csrf_input(); ?>
                            <button type="submit" name="accept_share" value="<?php echo (int)$row['id']; ?>" class="btn btn-sm btn-success">Accept</button>
                        </form>
                        <form method="post" style="display:inline;">
                            <?php echo csrf_input(); ?>
                            <button type="submit" name="decline_share" value="<?php echo (int)$row['id']; ?>" class="btn btn-sm btn-secondary">Decline</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-muted">No incoming share requests.</p>
        <?php endif; ?>

        <h3>Shared Stations</h3>
        <?php
        $sharedStmt = $pdo->prepare("SELECT ss.id, s.pk_serialNumber, s.name, s.description, s.fk_user_owns FROM station_share ss JOIN station s ON s.pk_serialNumber = ss.station_serial WHERE ss.shared_with = ? AND ss.status = 'accepted' ORDER BY s.pk_serialNumber");
        $sharedStmt->execute([$username]);
        $sharedStations = $sharedStmt->fetchAll();
        if (count($sharedStations) > 0):
        ?>
        <table>
            <thead>
                <tr>
                    <th>Serial</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Owner</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sharedStations as $station): ?>
                <tr>
                    <td><?php echo htmlspecialchars($station['pk_serialNumber']); ?></td>
                    <td><?php echo htmlspecialchars($station['name']); ?></td>
                    <td><?php echo htmlspecialchars($station['description'] ?? '-') ?: '-'; ?></td>
                    <td><?php echo htmlspecialchars($station['fk_user_owns']); ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <?php echo csrf_input(); ?>
                            <button type="submit" name="decline_share" value="<?php echo (int)$station['id']; ?>" class="btn btn-sm btn-secondary">Remove</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-muted">No shared stations yet.</p>
        <?php endif; ?>

        <h3>Shares You've Sent</h3>
        <?php
        $outStmt = $pdo->prepare("SELECT ss.id, ss.station_serial, ss.shared_with, ss.status, s.name FROM station_share ss JOIN station s ON s.pk_serialNumber = ss.station_serial WHERE ss.owner_user = ? ORDER BY ss.created_at DESC");
        $outStmt->execute([$username]);
        $outgoing = $outStmt->fetchAll();
        if (count($outgoing) > 0):
        ?>
        <table>
            <thead>
                <tr>
                    <th>Station</th>
                    <th>Friend</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($outgoing as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name'] . ' (' . $row['station_serial'] . ')'); ?></td>
                    <td><?php echo htmlspecialchars($row['shared_with']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td>
                        <?php if ($row['status'] !== 'declined'): ?>
                        <form method="post" style="display:inline;">
                            <?php echo csrf_input(); ?>
                            <button type="submit" name="revoke_share" value="<?php echo (int)$row['id']; ?>" class="btn btn-sm btn-danger">Revoke</button>
                        </form>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-muted">No outgoing shares yet.</p>
        <?php endif; ?>

        <h2>Claim a Station</h2>
        <div class="row">
            <div class="col-half">
                <div class="box">
                    <div class="box-header">Claim by Serial</div>
                    <form method="post">
                        <?php echo csrf_input(); ?>
                        <div class="form-group">
                            <label for="station_serial">Station Serial</label>
                            <input type="text" id="station_serial" name="station_serial" placeholder="e.g. SN-1001">
                        </div>
                        <button type="submit" name="claim_station" class="btn btn-success">Claim</button>
                    </form>
                </div>
            </div>
            <div class="col-half">
                <div class="box">
                    <div class="box-header">Select Available Station</div>
                    <form method="post">
                        <?php echo csrf_input(); ?>
                        <div class="form-group">
                            <label for="station_id">Available Stations</label>
                            <select id="station_id" name="station_id">
                                <option value="">Select a station</option>
                                <?php
                                $stmt = $pdo->query("SELECT pk_serialNumber, name FROM station WHERE fk_user_owns IS NULL");
                                while ($station = $stmt->fetch()) {
                                    echo "<option value='" . htmlspecialchars($station['pk_serialNumber']) . "'>" . htmlspecialchars($station['name']) . " (" . htmlspecialchars($station['pk_serialNumber']) . ")</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" name="claim_station" class="btn btn-success">Claim</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="box" style="max-width: 600px;">
            <div class="box-header">Claim by Provision Token (QR)</div>
            <p class="text-small">If you scanned a QR code, enter serial and token below.</p>
            <form method="post">
                <?php echo csrf_input(); ?>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="prov_serial">Station Serial</label>
                            <input type="text" id="prov_serial" name="prov_serial" placeholder="SN-1001">
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="prov_token">Provision Token</label>
                            <input type="text" id="prov_token" name="prov_token" placeholder="token">
                        </div>
                    </div>
                </div>
                <button type="submit" name="claim_with_provision" class="btn btn-success">Claim</button>
            </form>
        </div>
    </div>

<script>
// Populate and reveal the Edit Station form with provided values
function editStation(serial, name, desc) {
    $('#edit_serial').val(serial);
    $('#edit_name').val(name);
    $('#edit_description').val(desc);
    $('#editForm').show();
    $('html, body').animate({ scrollTop: $('#editForm').offset().top - 20 }, 300);
}
</script>
</body>
</html>