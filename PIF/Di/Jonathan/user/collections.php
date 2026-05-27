<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collections - Indoor Climate</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/app.js"></script>
</head>
<body>
    <?php
    // Collections: create, delete, and share measurement collections with friends
    $pageTitle = 'Collections';
    require_once '../config.php';
    require_once __DIR__ . '/../_header.php';
    require_once __DIR__ . '/../inc/csrf.php';

    // Check login
    if (!isset($_SESSION['username'])) {
        header('Location: ../login.php');
        exit;
    }
    $username = $_SESSION['username'];

    // Handle form submissions
    $message = '';
    $messageType = 'info';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['create_collection'])) {
            $name = trim($_POST['name']);
            $station_serials_raw = $_POST['stations'] ?? [];
            $start_raw = $_POST['start_date'] ?? null;
            $end_raw = $_POST['end_date'] ?? null;
            $start = $start_raw ? str_replace('T', ' ', $start_raw) : null;
            $end = $end_raw ? str_replace('T', ' ', $end_raw) : null;

            $station_serials = is_array($station_serials_raw) ? $station_serials_raw : [$station_serials_raw];
            $station_serials = array_values(array_unique(array_filter(array_map('trim', $station_serials), function ($v) {
                return $v !== '';
            })));

            if (!validate_csrf($_POST['csrf_token'] ?? '')) {
                $message = 'Invalid CSRF token.';
                $messageType = 'danger';
            } elseif ($name === '' || count($station_serials) === 0 || !$start || !$end) {
                $message = 'Please provide collection name, at least one station, and both start and end dates.';
                $messageType = 'warning';
            } elseif (strlen($name) > 255) {
                $message = 'Collection name too long (max 255 characters).';
                $messageType = 'warning';
            } else {
                foreach ($station_serials as $station_serial) {
                    if (!preg_match('/^[A-Za-z0-9_\-]{1,64}$/', $station_serial)) {
                        $message = 'Invalid station identifier selected.';
                        $messageType = 'danger';
                        break;
                    }
                }
            }

            if ($message === '') {
                // Validate datetime inputs
                $dt_start = DateTime::createFromFormat('Y-m-d H:i', $start);
                $dt_end = DateTime::createFromFormat('Y-m-d H:i', $end);
                if (!$dt_start || !$dt_end) {
                    $message = 'Invalid start or end date format.';
                    $messageType = 'warning';
                } elseif ($dt_start > $dt_end) {
                    $message = 'Start date must be before end date.';
                    $messageType = 'warning';
                } else {
                    // Normalize to full datetime strings with seconds
                    $start = $dt_start->format('Y-m-d H:i:s');
                    $end = $dt_end->format('Y-m-d H:i:s');
                    // Enforce access: users can only create collections from owned or shared stations
                    $placeholders = implode(',', array_fill(0, count($station_serials), '?'));
                    $access = $pdo->prepare("
                        SELECT s.pk_serialNumber
                        FROM station s
                        LEFT JOIN station_share ss
                            ON ss.station_serial = s.pk_serialNumber
                           AND ss.shared_with = ?
                           AND ss.status = 'accepted'
                        WHERE (s.fk_user_owns = ? OR ss.id IS NOT NULL)
                          AND s.pk_serialNumber IN ($placeholders)
                    ");
                    $access->execute(array_merge([$username, $username], $station_serials));
                    $allowed = $access->fetchAll(PDO::FETCH_COLUMN, 0);
                    if (count($allowed) !== count($station_serials)) {
                        $message = 'You can only create collections from stations you own or that are shared with you.';
                        $messageType = 'danger';
                    } else {
                        // Create collection
                        $stmt = $pdo->prepare("INSERT INTO collection (name, description, fk_user_creates) VALUES (?, ?, ?)");
                        $stmt->execute([$name, null, $username]);
                        $collection_id = $pdo->lastInsertId();

                        // Add measurements to collection using contains
                        $in = implode(',', array_fill(0, count($station_serials), '?'));
                        $insert = $pdo->prepare("INSERT INTO contains (pkfk_collection, pkfk_measurement) SELECT DISTINCT ?, m.pk_measurement FROM measurement m WHERE m.fk_station_records IN ($in) AND m.timestamp BETWEEN ? AND ?");
                        $insert->execute(array_merge([$collection_id], $station_serials, [$start, $end]));
                        $count = $insert->rowCount();
                        $message = 'Collection created! Measurements added: ' . $count;
                        $messageType = 'success';
                    }
                }
            }
        }

        if (isset($_POST['delete_collection'])) {
            if (!validate_csrf($_POST['csrf_token'] ?? '')) {
                $message = 'Invalid CSRF token.';
                $messageType = 'danger';
            } else {
                $id = intval($_POST['delete_collection']);
                if ($id <= 0) {
                    $message = 'Invalid collection id.';
                    $messageType = 'danger';
                } else {
                    $pdo->prepare("DELETE FROM collection WHERE pk_collection = ? AND fk_user_creates = ?")->execute([$id, $username]);
                    $message = 'Collection deleted.';
                    $messageType = 'success';
                }
            }
        }

        if (isset($_POST['share_collection'])) {
            if (!validate_csrf($_POST['csrf_token'] ?? '')) {
                $message = 'Invalid CSRF token.';
                $messageType = 'danger';
            } else {
                $collection_id = $_POST['collection_id'] ?? '';
                $friend_username = $_POST['friend_username'] ?? '';
                if ($collection_id === '' || $friend_username === '') {
                    $message = 'Select collection and friend to share with.';
                    $messageType = 'warning';
                } else {
                    // Basic validation
                    if (!ctype_digit(strval($collection_id))) {
                        $message = 'Invalid collection selected.';
                        $messageType = 'danger';
                    } elseif (!preg_match('/^[A-Za-z0-9_\-]{3,64}$/', $friend_username)) {
                        $message = 'Invalid friend username.';
                        $messageType = 'danger';
                    } else {
                        // Ensure user owns the collection
                        $own = $pdo->prepare('SELECT 1 FROM collection WHERE pk_collection = ? AND fk_user_creates = ?');
                        $own->execute([$collection_id, $username]);
                        if (!$own->fetchColumn()) {
                            $message = 'You can only share collections you created.';
                            $messageType = 'danger';
                        } else {
                            // Ensure recipient is actually a friend
                            $fr = $pdo->prepare('SELECT 1 FROM isfriend WHERE pkfk_user_user = ? AND pkfk_user_friend = ?');
                            $fr->execute([$username, $friend_username]);
                            if (!$fr->fetchColumn()) {
                                $message = 'You can only share with friends.';
                                $messageType = 'danger';
                            } else {
                                // Insert access row
                                $stmt = $pdo->prepare("INSERT INTO hasaccess (pkfk_user, pkfk_collection) VALUES (?, ?) ON DUPLICATE KEY UPDATE pkfk_user = pkfk_user");
                                try {
                                    $stmt->execute([$friend_username, $collection_id]);
                            // Handle delete collection form
                            if (isset($_POST['delete_collection'])) {
                                if (!validate_csrf($_POST['csrf_token'] ?? '')) {
                                    $message = 'Invalid CSRF token.';
                                    $messageType = 'danger';
                                } else {
                                    $id = intval($_POST['delete_collection']);
                                    if ($id <= 0) {
                                        $message = 'Invalid collection id.';
                                        $messageType = 'danger';
                                    } else {
                                        // Delete collection if user is owner
                                        $pdo->prepare("DELETE FROM collection WHERE pk_collection = ? AND fk_user_creates = ?")->execute([$id, $username]);
                                        $message = 'Collection deleted.';
                                        $messageType = 'success';
                                    }
                                }
                            }
                            // Handle share collection with friend form
                            if (isset($_POST['share_collection'])) {
                                if (!validate_csrf($_POST['csrf_token'] ?? '')) {
                                    $message = 'Invalid CSRF token.';
                                    $messageType = 'danger';
                                } else {
                                    $collection_id = $_POST['collection_id'] ?? '';
                                    $friend_username = $_POST['friend_username'] ?? '';
                                    if ($collection_id === '' || $friend_username === '') {
                                        $message = 'Select collection and friend to share with.';
                                        $messageType = 'warning';
                                    } else {
                                        // Validate collection and friend username
                                        if (!ctype_digit(strval($collection_id))) {
                                            $message = 'Invalid collection selected.';
                                            $messageType = 'danger';
                                        } elseif (!preg_match('/^[A-Za-z0-9_\-]{3,64}$/', $friend_username)) {
                                            $message = 'Invalid friend username.';
                                            $messageType = 'danger';
                                        } else {
                                            // Ensure user owns the collection
                                            $own = $pdo->prepare('SELECT 1 FROM collection WHERE pk_collection = ? AND fk_user_creates = ?');
                                            $own->execute([$collection_id, $username]);
                                            if (!$own->fetchColumn()) {
                                                $message = 'You can only share collections you created.';
                                                $messageType = 'danger';
                                            } else {
                                                // Ensure recipient is a friend
                                                $fr = $pdo->prepare('SELECT 1 FROM isfriend WHERE pkfk_user_user = ? AND pkfk_user_friend = ?');
                                                $fr->execute([$username, $friend_username]);
                                                if (!$fr->fetchColumn()) {
                                                    $message = 'You can only share with friends.';
                                                    $messageType = 'danger';
                                                } else {
                                                    // Grant access to friend in hasaccess table
                                                    $stmt = $pdo->prepare("INSERT INTO hasaccess (pkfk_user, pkfk_collection) VALUES (?, ?) ON DUPLICATE KEY UPDATE pkfk_user = pkfk_user");
                                                    try {
                                                        $stmt->execute([$friend_username, $collection_id]);
                                                        $message = 'Collection shared!';
                                                        $messageType = 'success';
                                                    } catch (PDOException $e) {
                                                        error_log('Share collection failed: ' . $e->getMessage());
                                                        $message = 'Failed to share collection. See server logs.';
                                                        $messageType = 'danger';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            // Handle leave shared collection form
                            if (isset($_POST['leave_collection'])) {
                                if (!validate_csrf($_POST['csrf_token'] ?? '')) {
                                    $message = 'Invalid CSRF token.';
                                    $messageType = 'danger';
                                } else {
                                    $collection_id = intval($_POST['leave_collection']);
                                    if ($collection_id <= 0) {
                                        $message = 'Invalid collection id.';
                                        $messageType = 'danger';
                                    } else {
                                        // Remove user from hasaccess table (leave shared collection)
                                        $stmt = $pdo->prepare("DELETE FROM hasaccess WHERE pkfk_collection = ? AND pkfk_user = ?");
                                        $stmt->execute([$collection_id, $username]);
                                        $message = 'You have left the shared collection.';
                                        $messageType = 'success';
                                    }
                                }
                            }
                            // Handle remove user from shared collection (by owner)
                            if (isset($_POST['remove_user'])) {
                                if (!validate_csrf($_POST['csrf_token'] ?? '')) {
                                    $message = 'Invalid CSRF token.';
                                    $messageType = 'danger';
                                } else {
                                    $collection_id = intval($_POST['collection_id']);
                                    $user_to_remove = $_POST['user_to_remove'];
                                    if ($collection_id <= 0 || empty($user_to_remove)) {
                                        $message = 'Invalid request.';
                                        $messageType = 'danger';
                                    } else {
                                        // Verify user owns the collection
                                        $stmt = $pdo->prepare("SELECT pk_collection FROM collection WHERE pk_collection = ? AND fk_user_creates = ?");
                                        $stmt->execute([$collection_id, $username]);
                                        if ($stmt->fetch()) {
                                            // Remove user from hasaccess table (revoke access)
                                            $stmt = $pdo->prepare("DELETE FROM hasaccess WHERE pkfk_collection = ? AND pkfk_user = ?");
                                            $stmt->execute([$collection_id, $user_to_remove]);
                                            $message = 'User removed from collection.';
                                            $messageType = 'success';
                                        } else {
                                            $message = 'You do not own this collection.';
                                            $messageType = 'danger';
                                        }
                                    }
                                }
                            }
                                    $message = 'Collection shared!';
                                    $messageType = 'success';
                                } catch (PDOException $e) {
                                    error_log('Share collection failed: ' . $e->getMessage());
                                    $message = 'Failed to share collection. See server logs.';
                                    $messageType = 'danger';
                                }
                            }
                        }
                    }
                }
            }
        }

        if (isset($_POST['leave_collection'])) {
            if (!validate_csrf($_POST['csrf_token'] ?? '')) {
                $message = 'Invalid CSRF token.';
                $messageType = 'danger';
            } else {
                $collection_id = intval($_POST['leave_collection']);
                if ($collection_id <= 0) {
                    $message = 'Invalid collection id.';
                    $messageType = 'danger';
                } else {
                    // Remove user from hasaccess table
                    $stmt = $pdo->prepare("DELETE FROM hasaccess WHERE pkfk_collection = ? AND pkfk_user = ?");
                    $stmt->execute([$collection_id, $username]);
                    $message = 'You have left the shared collection.';
                    $messageType = 'success';
                }
            }
        }

        if (isset($_POST['remove_user'])) {
            if (!validate_csrf($_POST['csrf_token'] ?? '')) {
                $message = 'Invalid CSRF token.';
                $messageType = 'danger';
            } else {
                $collection_id = intval($_POST['collection_id']);
                $user_to_remove = $_POST['user_to_remove'];
                if ($collection_id <= 0 || empty($user_to_remove)) {
                    $message = 'Invalid request.';
                    $messageType = 'danger';
                } else {
                    // Verify user owns the collection
                    $stmt = $pdo->prepare("SELECT pk_collection FROM collection WHERE pk_collection = ? AND fk_user_creates = ?");
                    $stmt->execute([$collection_id, $username]);
                    if ($stmt->fetch()) {
                        // Remove user from hasaccess table
                        $stmt = $pdo->prepare("DELETE FROM hasaccess WHERE pkfk_collection = ? AND pkfk_user = ?");
                        $stmt->execute([$collection_id, $user_to_remove]);
                        $message = 'User removed from collection.';
                        $messageType = 'success';
                    } else {
                        $message = 'You do not own this collection.';
                        $messageType = 'danger';
                    }
                }
            }
        }
    }
    ?>
    <div class="container">
        <h1>Collections</h1>
        <p class="text-muted">Manage your collections and view shared collections from friends</p>
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="box">
            <div class="box-header">Create New Collection</div>
            <form method="post">
                <?php echo csrf_input(); ?>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="name">Collection Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="stations">Stations</label>
                            <select id="stations" name="stations[]" multiple required>
                                <?php
                                $stmt = $pdo->prepare("
                                    SELECT s.pk_serialNumber, s.name, s.fk_user_owns,
                                           CASE WHEN s.fk_user_owns = ? THEN 1 ELSE 0 END AS is_owner
                                    FROM station s
                                    LEFT JOIN station_share ss
                                        ON ss.station_serial = s.pk_serialNumber
                                       AND ss.shared_with = ?
                                       AND ss.status = 'accepted'
                                    WHERE s.fk_user_owns = ? OR ss.id IS NOT NULL
                                    ORDER BY is_owner DESC, s.pk_serialNumber
                                ");
                                $stmt->execute([$username, $username, $username]);
                                while ($station = $stmt->fetch()) {
                                    $label = $station['name'] . ' (' . $station['pk_serialNumber'] . ')';
                                    if ((int)$station['is_owner'] !== 1) {
                                        $label .= ' - shared by ' . $station['fk_user_owns'];
                                    }
                                    echo "<option value='" . htmlspecialchars($station['pk_serialNumber']) . "'>" . htmlspecialchars($label) . "</option>";
                                }
                                ?>
                            </select>
                            <span class="text-small text-muted">Hold Ctrl/Cmd to select multiple</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="datetime-local" id="start_date" name="start_date" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="end_date">End Date</label>
                            <input type="datetime-local" id="end_date" name="end_date" required>
                        </div>
                    </div>
                </div>
                <button type="submit" name="create_collection" class="btn btn-success">Create Collection</button>
            </form>
        </div>

        <h2>Your Collections</h2>
        <?php
        // Get both owned collections and shared collections
        $stmt = $pdo->prepare("
            SELECT c.pk_collection, c.name, c.fk_user_creates,
                   CASE WHEN c.fk_user_creates = ? THEN 'owned' ELSE 'shared' END as type,
                   u.firstName, u.lastName
            FROM collection c
            LEFT JOIN hasaccess h ON c.pk_collection = h.pkfk_collection
            LEFT JOIN `user` u ON c.fk_user_creates = u.pk_username
            WHERE c.fk_user_creates = ? OR h.pkfk_user = ?
            ORDER BY c.pk_collection DESC
        ");
        $stmt->execute([$username, $username, $username]);
        $collections = $stmt->fetchAll();

        if (count($collections) > 0):
        ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Owner</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($collections as $collection):
                    $id = $collection['pk_collection'];
                    $name = htmlspecialchars($collection['name']);
                    $type = $collection['type'];
                    $ownerName = htmlspecialchars(trim(($collection['firstName'] ?? '') . ' ' . ($collection['lastName'] ?? '')));
                    $isOwner = ($type === 'owned');
                ?>
                <tr>
                    <td><?php echo $name; ?></td>
                    <td><?php echo $isOwner ? 'Owned' : 'Shared'; ?></td>
                    <td><?php echo $isOwner ? 'You' : $ownerName; ?></td>
                    <td>
                        <a href="view_collection.php?id=<?php echo $id; ?>" class="btn btn-sm btn-primary">View</a>
                        <?php if ($isOwner): ?>
                        <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $id; ?>, '<?php echo addslashes($name); ?>')">Delete</button>
                        <?php else: ?>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="leaveCollection(<?php echo $id; ?>, '<?php echo addslashes($name); ?>')">Leave</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-muted">You don't have any collections yet. Create one above.</p>
        <?php endif; ?>

        <h2>Share a Collection</h2>
        <div class="box" style="max-width: 500px;">
            <form method="post">
                <?php echo csrf_input(); ?>
                <div class="form-group">
                    <label for="collection_id">Collection</label>
                    <select id="collection_id" name="collection_id" required>
                        <option value="">Select a collection</option>
                        <?php
                        $stmt = $pdo->prepare("SELECT pk_collection, name FROM collection WHERE fk_user_creates = ?");
                        $stmt->execute([$username]);
                        while ($collection = $stmt->fetch()) {
                            echo "<option value='" . $collection['pk_collection'] . "'>" . htmlspecialchars($collection['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="friend_username">Friend</label>
                    <select id="friend_username" name="friend_username" required>
                        <option value="">Select a friend</option>
                        <?php
                        $stmt = $pdo->prepare("SELECT u.pk_username FROM `user` u JOIN isfriend f ON u.pk_username = f.pkfk_user_friend WHERE f.pkfk_user_user = ?");
                        $stmt->execute([$username]);
                        while ($friend = $stmt->fetch()) {
                            echo "<option value='" . htmlspecialchars($friend['pk_username']) . "'>" . htmlspecialchars($friend['pk_username']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" name="share_collection" class="btn btn-success">Share</button>
            </form>
        </div>
    </div>

    <form id="deleteForm" method="post" style="display: none;">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="delete_collection" id="deleteId">
    </form>

    <form id="leaveForm" method="post" style="display: none;">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="leave_collection" id="leaveId">
    </form>

    <script>
    // Show confirmation and submit form to delete a collection (owner action)
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Delete Collection?',
            text: 'Are you sure you want to delete "' + name + '"?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!'
        }).then(function(result) {
            if (result.isConfirmed) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        });
    }

    // Prompt confirmation and submit form to leave a shared collection
    function leaveCollection(id, name) {
        Swal.fire({
            title: 'Leave Collection?',
            text: 'Are you sure you want to leave "' + name + '"?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, leave it'
        }).then(function(result) {
            if (result.isConfirmed) {
                document.getElementById('leaveId').value = id;
                document.getElementById('leaveForm').submit();
            }
        });
    }
    </script>
</body>
</html>