<?php
session_start();
require __DIR__ . '/assets/db.php';

if (!isset($_SESSION['username'])) {
    header('Location: /login.php');
    exit;
}

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$username = $_SESSION['username'];

function parseDate(string $value): ?DateTime
{
    return DateTime::createFromFormat('Y-m-d H:i:s', $value)
        ?: DateTime::createFromFormat(DateTime::ATOM, $value)
        ?: DateTime::createFromFormat('Y-m-d\TH:i', $value);
}

$pdo = getDb();
$errors = $_SESSION['flash_errors'] ?? [];
$success = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_errors'], $_SESSION['flash_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'create') {
            $name = trim($_POST['name'] ?? '');
            $station = trim($_POST['station'] ?? '');
            $startRaw = trim($_POST['start'] ?? '');
            $endRaw = trim($_POST['end'] ?? '');

            if (!$name || !$station || !$startRaw || !$endRaw) {
                $errors[] = 'Name, station, start, and end are required.';
            } else {
                $own = $pdo->prepare('SELECT pk_serialNumber FROM station WHERE pk_serialNumber = :sn AND fk_user_owns = :u');
                $own->execute([':sn' => $station, ':u' => $username]);
                if (!$own->fetch()) {
                    $errors[] = 'You can only use your own stations.';
                } else {
                    $start = parseDate($startRaw);
                    $end = parseDate($endRaw);
                    if (!$start || !$end) {
                        $errors[] = 'Invalid start or end date/time.';
                    } elseif ($start > $end) {
                        $errors[] = 'Start must be before end.';
                    } else {
                        $pdo->beginTransaction();
                        $meta = json_encode([
                            'station' => $station,
                            'start' => $start->format('Y-m-d H:i:s'),
                            'end' => $end->format('Y-m-d H:i:s'),
                        ]);
                        $ins = $pdo->prepare('INSERT INTO collection (name, description, fk_user_creates) VALUES (:n, :d, :u)');
                        $ins->execute([':n' => $name, ':d' => $meta, ':u' => $username]);
                        $cid = (int) $pdo->lastInsertId();

                        $mStmt = $pdo->prepare('SELECT pk_measurement FROM measurement WHERE fk_station_records = :st AND timestamp BETWEEN :s AND :e');
                        $mStmt->execute([
                            ':st' => $station,
                            ':s' => $start->format('Y-m-d H:i:s'),
                            ':e' => $end->format('Y-m-d H:i:s'),
                        ]);
                        $ids = $mStmt->fetchAll();
                        if ($ids) {
                            $link = $pdo->prepare('INSERT IGNORE INTO contains (pkfk_collection, pkfk_measurement) VALUES (:c, :m)');
                            foreach ($ids as $row) {
                                $link->execute([':c' => $cid, ':m' => $row['pk_measurement']]);
                            }
                        }

                        $pdo->commit();
                        $_SESSION['flash_success'] = 'Collection created and measurements added.';
                    }
                }
            }
        } elseif ($action === 'rename') {
            $cid = (int) ($_POST['collectionId'] ?? 0);
            $newName = trim($_POST['name'] ?? '');
            if (!$cid || !$newName) {
                $errors[] = 'Collection and new name are required.';
            } else {
                $upd = $pdo->prepare('UPDATE collection SET name = :n WHERE pk_collection = :c AND fk_user_creates = :u');
                $upd->execute([':n' => $newName, ':c' => $cid, ':u' => $username]);
                if ($upd->rowCount() === 0) {
                    $errors[] = 'Rename failed (not found or not owner).';
                } else {
                    $_SESSION['flash_success'] = 'Collection renamed.';
                }
            }
        } elseif ($action === 'delete') {
            $cid = (int) ($_POST['collectionId'] ?? 0);
            if (!$cid) {
                $errors[] = 'Collection is required.';
            } else {
                $pdo->beginTransaction();
                $pdo->prepare('DELETE FROM hasaccess WHERE pkfk_collection = :c')->execute([':c' => $cid]);
                $pdo->prepare('DELETE FROM contains WHERE pkfk_collection = :c')->execute([':c' => $cid]);
                $del = $pdo->prepare('DELETE FROM collection WHERE pk_collection = :c AND fk_user_creates = :u');
                $del->execute([':c' => $cid, ':u' => $username]);
                $pdo->commit();
                if ($del->rowCount() === 0) {
                    $errors[] = 'Delete failed (not found or not owner).';
                } else {
                    $_SESSION['flash_success'] = 'Collection deleted.';
                }
            }
        } elseif ($action === 'share') {
            $cid = (int) ($_POST['collectionId'] ?? 0);
            $to = trim($_POST['to'] ?? '');
            if (!$cid || !$to) {
                $errors[] = 'Collection and recipient are required.';
            } else {
                $own = $pdo->prepare('SELECT pk_collection FROM collection WHERE pk_collection = :c AND fk_user_creates = :u');
                $own->execute([':c' => $cid, ':u' => $username]);
                if (!$own->fetch()) {
                    $errors[] = 'You do not own that collection.';
                } else {
                    $friend = $pdo->prepare('SELECT 1 FROM isfriend WHERE ((pkfk_user_user = :me AND pkfk_user_friend = :to) OR (pkfk_user_user = :to AND pkfk_user_friend = :me)) AND isaccepted = 1');
                    $friend->execute([':me' => $username, ':to' => $to]);
                    if (!$friend->fetch()) {
                        $errors[] = 'You can only share with friends.';
                    } else {
                        $pdo->prepare('INSERT IGNORE INTO hasaccess (pkfk_user, pkfk_collection) VALUES (:u, :c)')->execute([':u' => $to, ':c' => $cid]);
                        $_SESSION['flash_success'] = 'Collection shared.';
                    }
                }
            }
        } elseif ($action === 'unshare') {
            $cid = (int) ($_POST['collectionId'] ?? 0);
            $to = trim($_POST['to'] ?? '');
            if (!$cid || !$to) {
                $errors[] = 'Collection and user are required.';
            } else {
                $own = $pdo->prepare('SELECT pk_collection FROM collection WHERE pk_collection = :c AND fk_user_creates = :u');
                $own->execute([':c' => $cid, ':u' => $username]);
                if (!$own->fetch()) {
                    $errors[] = 'You do not own that collection.';
                } else {
                    $pdo->prepare('DELETE FROM hasaccess WHERE pkfk_collection = :c AND pkfk_user = :u')->execute([':c' => $cid, ':u' => $to]);
                    $_SESSION['flash_success'] = 'Sharing removed.';
                }
            }
        }

        if ($errors) {
            $_SESSION['flash_errors'] = $errors;
        }

        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $errors[] = 'Database error: ' . $e->getMessage();
    }
}

$stationsStmt = $pdo->prepare('SELECT pk_serialNumber, name FROM station WHERE fk_user_owns = :u ORDER BY name');
$stationsStmt->execute([':u' => $username]);
$stations = $stationsStmt->fetchAll();

$ownedStmt = $pdo->prepare('SELECT pk_collection, name, description FROM collection WHERE fk_user_creates = :u ORDER BY pk_collection DESC');
$ownedStmt->execute([':u' => $username]);
$ownedCollections = $ownedStmt->fetchAll();

// Preload measurements for owned collections
$ownedIds = array_column($ownedCollections, 'pk_collection');
$measurementsByCollection = [];
if ($ownedIds) {
    $placeholders = implode(',', array_fill(0, count($ownedIds), '?'));
    $mQuery = $pdo->prepare(
        "SELECT c.pkfk_collection, m.pk_measurement, m.timestamp, m.temperature, m.humidity, m.pressure, m.light, m.gas
         FROM contains c
         JOIN measurement m ON m.pk_measurement = c.pkfk_measurement
         WHERE c.pkfk_collection IN ($placeholders)
         ORDER BY m.timestamp DESC"
    );
    $mQuery->execute($ownedIds);
    while ($row = $mQuery->fetch(PDO::FETCH_ASSOC)) {
        $measurementsByCollection[$row['pkfk_collection']][] = $row;
    }
}

$sharedCollections = [];
$sharedWithStmt = $pdo->prepare('SELECT c.pk_collection, c.name, c.description, c.fk_user_creates
                                 FROM hasaccess ha
                                 JOIN collection c ON ha.pkfk_collection = c.pk_collection
                                 WHERE ha.pkfk_user = :u
                                 ORDER BY c.pk_collection DESC');
$sharedWithStmt->execute([':u' => $username]);
$sharedCollections = $sharedWithStmt->fetchAll();

// Optional: include legacy collection_share table if present (supports pk_collectionID/fk_ownerUsername schema)
foreach ([
    'SELECT c.pk_collection AS pk_collection, c.name, c.description, c.fk_user_creates AS fk_user_creates
     FROM collection_share cs
     JOIN collection c ON cs.fk_collectionID = c.pk_collection
     WHERE cs.shared_with_username = :u
     ORDER BY pk_collection DESC',
    'SELECT c.pk_collectionID AS pk_collection, c.name, c.description, c.fk_ownerUsername AS fk_user_creates
     FROM collection_share cs
     JOIN collection c ON cs.fk_collectionID = c.pk_collectionID
     WHERE cs.shared_with_username = :u
     ORDER BY pk_collection DESC'
] as $legacySql) {
    try {
        $legacy = $pdo->prepare($legacySql);
        $legacy->execute([':u' => $username]);
        $legacyRows = $legacy->fetchAll();
        if ($legacyRows) {
            $byId = [];
            foreach (array_merge($sharedCollections, $legacyRows) as $row) {
                $byId[$row['pk_collection']] = $row;
            }
            $sharedCollections = array_values($byId);
            break;
        }
    } catch (PDOException $e) {
        // try next variant
        continue;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/style.css">
    <title>Collections</title>
</head>
<body>
    <?php include __DIR__ . '/assets/header.php'; ?>
    <main class="container">
        <h1>Your Collections</h1>

        <?php if ($errors): ?>
            <div class="alert danger">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo h($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif ($success): ?>
            <div class="alert success"><?php echo h($success); ?></div>
        <?php endif; ?>

        <section id="create-collection">
            <h2 class="section-title">Create Collection</h2>
            <div class="card">
                <form method="post" class="create-form">
                    <input type="hidden" name="action" value="create">
                    <div class="form-grid">
                        <label class="field-label" for="collection-name">Name</label>
                        <input class="input-text" id="collection-name" name="name" type="text" required>

                        <label class="field-label" for="collection-station">Station</label>
                        <select class="input-select" id="collection-station" name="station" required>
                            <option value="">Select a station</option>
                            <?php foreach ($stations as $station): ?>
                                <?php $label = $station['name'] ? h($station['name']) . ' (' . h($station['pk_serialNumber']) . ')' : h($station['pk_serialNumber']); ?>
                                <option value="<?php echo h($station['pk_serialNumber']); ?>"><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label class="field-label" for="collection-start">Start</label>
                        <input class="input-text" id="collection-start" name="start" type="datetime-local" required>

                        <label class="field-label" for="collection-end">End</label>
                        <input class="input-text" id="collection-end" name="end" type="datetime-local" required>

                        <div></div>
                        <button class="primary-btn" type="submit">Create</button>
                    </div>
                </form>
            </div>
        </section>

        <section id="your-collections">
            <h2 class="section-title">Your Collections</h2>
            <?php if (!$ownedCollections): ?>
                <p class="info-text">No collections yet.</p>
            <?php else: ?>
                <details open class="card">
                    <summary><strong><?php echo count($ownedCollections); ?></strong> collection(s)</summary>
                    <div class="cards" style="margin-top:12px;">
                <?php foreach ($ownedCollections as $col): ?>
                    <?php
                    $meta = json_decode($col['description'] ?? '', true) ?: [];
                    $sharedStmt = $pdo->prepare('SELECT pkfk_user FROM hasaccess WHERE pkfk_collection = :c ORDER BY pkfk_user');
                    $sharedStmt->execute([':c' => $col['pk_collection']]);
                    $sharedWith = array_column($sharedStmt->fetchAll(), 'pkfk_user');
                    ?>
                    <details class="card" open>
                        <summary><strong><?php echo h($col['name']); ?></strong></summary>
                        <div class="muted" style="margin-top:6px;">
                            Station: <?php echo h($meta['station'] ?? ''); ?>
                            | Start: <?php echo h($meta['start'] ?? ''); ?>
                            | End: <?php echo h($meta['end'] ?? ''); ?>
                        </div>

                        <?php $ms = $measurementsByCollection[$col['pk_collection']] ?? []; ?>
                        <div class="muted" style="margin-top: 6px;">Measurements in range: <?php echo count($ms); ?></div>
                        <?php if ($ms): ?>
                            <div class="table-wrapper">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Timestamp</th>
                                            <th>Temp</th>
                                            <!--<th>Humidity</th> -->
                                            <th>Pressure</th>
                                            <th>Light</th>
                                            <th>Gas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ms as $row): ?>
                                            <tr>
                                                <td><?php echo h($row['timestamp']); ?></td>
                                                <td><?php echo h($row['temperature']); ?></td>
                                                <!--<td><?php echo h($row['humidity']); ?></td> -->
                                                <td><?php echo h($row['pressure']); ?></td>
                                                <td><?php echo h($row['light']); ?></td>
                                                <td><?php echo h($row['gas']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                        <form method="post" style="margin-top: 8px; display:flex; gap:8px; align-items:center;">
                            <input type="hidden" name="action" value="rename">
                            <input type="hidden" name="collectionId" value="<?php echo h($col['pk_collection']); ?>">
                            <input class="input-text" name="name" type="text" placeholder="New name" required>
                            <button class="primary-btn" type="submit">Rename</button>
                        </form>

                        <form method="post" style="margin-top: 8px; display:flex; gap:8px; align-items:center;">
                            <input type="hidden" name="action" value="share">
                            <input type="hidden" name="collectionId" value="<?php echo h($col['pk_collection']); ?>">
                            <input class="input-text" name="to" type="text" placeholder="Friend username" required>
                            <button class="primary-btn" type="submit">Share</button>
                        </form>

                        <?php if ($sharedWith): ?>
                            <div class="muted" style="margin-top: 8px;">Shared with: <?php echo h(implode(', ', $sharedWith)); ?></div>
                            <ul>
                                <?php foreach ($sharedWith as $user): ?>
                                    <li>
                                        <?php echo h($user); ?>
                                        <form method="post" style="display:inline-block; margin-left:6px;">
                                            <input type="hidden" name="action" value="unshare">
                                            <input type="hidden" name="collectionId" value="<?php echo h($col['pk_collection']); ?>">
                                            <input type="hidden" name="to" value="<?php echo h($user); ?>">
                                            <button class="danger-btn" type="submit">Unshare</button>
                                        </form>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="muted" style="margin-top: 8px;">Not shared with anyone.</div>
                        <?php endif; ?>

                        <form method="post" style="margin-top: 8px;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="collectionId" value="<?php echo h($col['pk_collection']); ?>">
                            <button class="danger-btn" type="submit" onclick="return confirm('Delete this collection?');">Delete</button>
                        </form>
                    </details>
                <?php endforeach; ?>
                    </div>
                </details>
            <?php endif; ?>
        </section>

        <section id="shared-collections">
            <h2 class="section-title">Collections Shared With You</h2>
            <?php if (!$sharedCollections): ?>
                <p class="info-text">No collections shared with you.</p>
            <?php else: ?>
                <div class="card">
                    <div class="muted"><strong><?php echo count($sharedCollections); ?></strong> shared collection(s)</div>
                    <div class="cards" style="margin-top:12px;">
                    <?php foreach ($sharedCollections as $col): ?>
                        <?php $meta = json_decode($col['description'] ?? '', true) ?: []; ?>
                        <details class="card" open>
                            <summary><strong><?php echo h($col['name']); ?></strong> (by <?php echo h($col['fk_user_creates']); ?>)</summary>
                            <div class="muted" style="margin-top:6px;">
                                Station: <?php echo h($meta['station'] ?? ''); ?>
                                | Start: <?php echo h($meta['start'] ?? ''); ?>
                                | End: <?php echo h($meta['end'] ?? ''); ?>
                            </div>
                        </details>
                    <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </main>
  <?php include 'assets/footer.php'; ?>

</body>
</html>
