<?php
$pageTitle = 'Manage Collections';
require_once __DIR__ . '/_header.php';

require_once __DIR__ . '/../inc/csrf.php';

$adminUser = $_SESSION['username'];

$message = '';
$messageType = 'info';

// Quick HTML-escape helper for safe output in templates
function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create (admin can pick any station)
    if (isset($_POST['create_collection'])) {
        if (!validate_csrf($_POST['csrf_token'] ?? '')) {
            $message = 'Invalid CSRF token.';
            $messageType = 'danger';
        } else {
            $name = trim($_POST['name'] ?? '');
            $station = trim($_POST['station'] ?? '');
            $startRaw = trim($_POST['start_date'] ?? '');
            $endRaw = trim($_POST['end_date'] ?? '');

            if ($name === '' || $station === '' || $startRaw === '' || $endRaw === '') {
                $message = 'All fields are required.';
                $messageType = 'warning';
            } elseif (strlen($name) > 255) {
                $message = 'Collection name too long (max 255 characters).';
                $messageType = 'warning';
            } elseif (!preg_match('/^[A-Za-z0-9_\-]{1,64}$/', $station)) {
                $message = 'Invalid station identifier.';
                $messageType = 'danger';
            } else {
                $dtStart = DateTime::createFromFormat('Y-m-d\TH:i', $startRaw);
                $dtEnd = DateTime::createFromFormat('Y-m-d\TH:i', $endRaw);
                if (!$dtStart || !$dtEnd) {
                    $message = 'Invalid start or end date format.';
                    $messageType = 'warning';
                } elseif ($dtStart > $dtEnd) {
                    $message = 'Start date must be before end date.';
                    $messageType = 'warning';
                } else {
                    // Ensure station exists
                    $chk = $pdo->prepare('SELECT 1 FROM station WHERE pk_serialNumber = ?');
                    $chk->execute([$station]);
                    if (!$chk->fetchColumn()) {
                        $message = 'Station not found.';
                        $messageType = 'danger';
                    } else {
                        $start = $dtStart->format('Y-m-d H:i:s');
                        $end = $dtEnd->format('Y-m-d H:i:s');

                        try {
                            $pdo->beginTransaction();
                            $ins = $pdo->prepare('INSERT INTO collection (name, description, fk_user_creates) VALUES (?, ?, ?)');
                            $ins->execute([$name, null, $adminUser]);
                            $collectionId = $pdo->lastInsertId();

                            $add = $pdo->prepare('INSERT INTO contains (pkfk_collection, pkfk_measurement)
                                SELECT ?, m.pk_measurement
                                FROM measurement m
                                WHERE m.fk_station_records = ? AND m.timestamp BETWEEN ? AND ?');
                            $add->execute([$collectionId, $station, $start, $end]);
                            $count = $add->rowCount();

                            $pdo->commit();
                            $message = 'Collection created. Measurements added: ' . $count;
                            $messageType = 'success';
                        } catch (Exception $e) {
                            $pdo->rollBack();
                            error_log('Admin create collection failed: ' . $e->getMessage());
                            $message = 'Failed to create collection.';
                            $messageType = 'danger';
                        }
                    }
                }
            }
        }
    }

    // Rename
    if (isset($_POST['rename_collection'])) {
        if (!validate_csrf($_POST['csrf_token'] ?? '')) {
            $message = 'Invalid CSRF token.';
            $messageType = 'danger';
        } else {
            $id = intval($_POST['collection_id'] ?? 0);
            $newName = trim($_POST['new_name'] ?? '');
            if ($id <= 0 || $newName === '') {
                $message = 'Invalid rename request.';
                $messageType = 'danger';
            } elseif (strlen($newName) > 255) {
                $message = 'New name too long (max 255 characters).';
                $messageType = 'warning';
            } else {
                $upd = $pdo->prepare('UPDATE collection SET name = ? WHERE pk_collection = ?');
                $upd->execute([$newName, $id]);
                $message = $upd->rowCount() > 0 ? 'Collection renamed.' : 'No change (collection not found?).';
                $messageType = $upd->rowCount() > 0 ? 'success' : 'warning';
            }
        }
    }

    // Delete
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
                $del = $pdo->prepare('DELETE FROM collection WHERE pk_collection = ?');
                $del->execute([$id]);
                $message = $del->rowCount() > 0 ? 'Collection deleted.' : 'Collection not found (already deleted?).';
                $messageType = $del->rowCount() > 0 ? 'success' : 'warning';
            }
        }
    }
}

// Data for page
$stations = [];
try {
    $stations = $pdo->query('SELECT pk_serialNumber, name FROM station ORDER BY pk_serialNumber')->fetchAll();
} catch (Exception $e) {
    error_log('Admin stations list failed: ' . $e->getMessage());
}

$collections = [];
try {
    $stmt = $pdo->query("SELECT c.pk_collection, c.name, c.fk_user_creates,
        (SELECT COUNT(*) FROM contains x WHERE x.pkfk_collection = c.pk_collection) AS measurement_count
        FROM collection c
        ORDER BY c.pk_collection DESC");
    $collections = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Admin collections list failed: ' . $e->getMessage());
}
?>

<div class="container">
    <h1>Collections</h1>

    <?php if ($message !== ''): ?>
        <div class="alert alert-<?php echo h($messageType); ?>">
            <?php echo h($message); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h3>Create Collection (Admin)</h3>
        <form method="post">
            <?php echo csrf_input(); ?>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="station">Station</label>
                <select id="station" name="station" required>
                    <option value="">Select station</option>
                    <?php foreach ($stations as $s): ?>
                        <?php $val = $s['pk_serialNumber']; ?>
                        <option value="<?php echo h($val); ?>"><?php echo h(($s['name'] ?? '') . ' (' . $val . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="start_date">Start</label>
                <input type="datetime-local" id="start_date" name="start_date" required>
            </div>
            <div class="form-group">
                <label for="end_date">End</label>
                <input type="datetime-local" id="end_date" name="end_date" required>
            </div>
            <button class="btn" type="submit" name="create_collection" value="1">Create</button>
        </form>
        <p><small>Creates a new collection owned by you and fills it with measurements from the selected station and time range.</small></p>
    </div>

    <div class="card">
        <h3>All Collections</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Owner</th>
                    <th>Measurements</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($collections) === 0): ?>
                    <tr><td colspan="5">No collections found.</td></tr>
                <?php else: ?>
                    <?php foreach ($collections as $c): ?>
                        <tr>
                            <td><?php echo h($c['pk_collection']); ?></td>
                            <td><?php echo h($c['name']); ?></td>
                            <td><?php echo h($c['fk_user_creates']); ?></td>
                            <td><?php echo h($c['measurement_count']); ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <?php echo csrf_input(); ?>
                                    <input type="hidden" name="collection_id" value="<?php echo h($c['pk_collection']); ?>">
                                    <input type="text" name="new_name" placeholder="New name" required style="width:120px;">
                                    <button class="btn btn-small" type="submit" name="rename_collection" value="1">Rename</button>
                                </form>
                                <form method="post" style="display:inline;" onsubmit="return confirm('Delete this collection?');">
                                    <?php echo csrf_input(); ?>
                                    <button class="btn btn-danger btn-small" type="submit" name="delete_collection" value="<?php echo h($c['pk_collection']); ?>">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <p><small>Admins can rename/delete any collection. Sharing is still only available for collections you created (via the user Collections page).</small></p>
    </div>
</div>
</body>
</html>
