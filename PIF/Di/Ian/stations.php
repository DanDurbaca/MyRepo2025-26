<?php
session_start();
require __DIR__ . '/assets/db.php';
require_once __DIR__ . '/assets/mailer.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$dbError = '';
$successMsg = '';
$userStations = [];

try {
    $pdo = getDb();

    // Handle station update (edit name/description)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'update') {
            $stationId = $_POST['station_id'] ?? '';
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if (!$stationId) {
                $dbError = 'Station ID is required.';
            } else {
                // Check if station belongs to user
                $checkStmt = $pdo->prepare(
                    'SELECT fk_user_owns FROM station WHERE pk_serialNumber = :id'
                );
                $checkStmt->execute([':id' => $stationId]);
                $station = $checkStmt->fetch();

                if (!$station || $station['fk_user_owns'] !== $_SESSION['username']) {
                    $dbError = 'You do not have permission to edit this station.';
                } else {
                    $updateStmt = $pdo->prepare(
                        'UPDATE station SET name = :name, description = :description WHERE pk_serialNumber = :id'
                    );
                    $updateStmt->execute([
                        ':name' => $name ?: null,
                        ':description' => $description ?: null,
                        ':id' => $stationId,
                    ]);
                    $successMsg = 'Station updated successfully.';
                    $dbError = '';
                    // Redirect to avoid form resubmission
                    header('Location: stations.php');
                    exit;
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            $stationId = $_POST['station_id'] ?? '';
            if (!$stationId) {
                $dbError = 'Station ID is required to delete.';
            } else {
                $checkStmt = $pdo->prepare('SELECT pk_serialNumber, name, fk_user_owns FROM station WHERE pk_serialNumber = :id');
                $checkStmt->execute([':id' => $stationId]);
                $station = $checkStmt->fetch();
                if (!$station || $station['fk_user_owns'] !== $_SESSION['username']) {
                    $dbError = 'You do not have permission to delete this station.';
                } else {
                    $stationName = $station['name'];
                    $delStmt = $pdo->prepare('DELETE FROM station WHERE pk_serialNumber = :id');
                    $delStmt->execute([':id' => $stationId]);
                    $successMsg = 'Station deleted successfully.';
                    $dbError = '';
                    // Notify user via email
                    try {
                        $uStmt = $pdo->prepare('SELECT firstName, email FROM user WHERE pk_username = :u');
                        $uStmt->execute([':u' => $_SESSION['username']]);
                        if ($u = $uStmt->fetch(PDO::FETCH_ASSOC)) {
                            if (!empty($u['email'])) {
                                @send_station_deleted_email($u['email'], $u['firstName'] ?? $_SESSION['username'], $stationId, $stationName);
                            }
                        }
                    } catch (Exception $e) {
                        error_log('station deletion email notify failed: ' . $e->getMessage());
                    }
                    header('Location: stations.php');
                    exit;
                }
            }
        } elseif ($_POST['action'] === 'register') {
            $stationId = trim($_POST['station_serial'] ?? '');

            if (!$stationId) {
                $dbError = 'Serial number is required.';
            } else {
                // Check if station exists
                $checkStmt = $pdo->prepare(
                    'SELECT fk_user_owns FROM station WHERE pk_serialNumber = :id'
                );
                $checkStmt->execute([':id' => $stationId]);
                $station = $checkStmt->fetch();

                if ($station) {
                    // Station exists, check if unassigned
                    if ($station['fk_user_owns']) {
                        $dbError = 'This station is already assigned to another user.';
                    } else {
                        // Assign to user
                        $assignStmt = $pdo->prepare(
                            'UPDATE station SET fk_user_owns = :user WHERE pk_serialNumber = :id'
                        );
                        $assignStmt->execute([
                            ':user' => $_SESSION['username'],
                            ':id' => $stationId,
                        ]);
                        $successMsg = 'Station registered successfully.';
                        $dbError = '';
                        // Notify user via email
                        try {
                            $uStmt = $pdo->prepare('SELECT firstName, email FROM user WHERE pk_username = :u');
                            $uStmt->execute([':u' => $_SESSION['username']]);
                            if ($u = $uStmt->fetch(PDO::FETCH_ASSOC)) {
                                if (!empty($u['email'])) {
                                    @send_station_added_email($u['email'], $u['firstName'] ?? $_SESSION['username'], $stationId, null);
                                }
                            }
                        } catch (Exception $e) {
                            error_log('station email notify failed: ' . $e->getMessage());
                        }
                        // Redirect to avoid form resubmission
                        header('Location: stations.php');
                        exit;
                    }
                } else {
                    // Station doesn't exist, create it and assign to user
                    $createStmt = $pdo->prepare(
                        'INSERT INTO station (pk_serialNumber, fk_user_owns) VALUES (:id, :user)'
                    );
                    $createStmt->execute([
                        ':id' => $stationId,
                        ':user' => $_SESSION['username'],
                    ]);
                    $successMsg = 'New station created and registered successfully.';
                    $dbError = '';
                    // Notify user via email
                    try {
                        $uStmt = $pdo->prepare('SELECT firstName, email FROM user WHERE pk_username = :u');
                        $uStmt->execute([':u' => $_SESSION['username']]);
                        if ($u = $uStmt->fetch(PDO::FETCH_ASSOC)) {
                            if (!empty($u['email'])) {
                                @send_station_added_email($u['email'], $u['firstName'] ?? $_SESSION['username'], $stationId, null);
                            }
                        }
                    } catch (Exception $e) {
                        error_log('station email notify failed: ' . $e->getMessage());
                    }
                    // Redirect to avoid form resubmission
                    header('Location: stations.php');
                    exit;
                }
            }
        }
    }

    // Fetch user's own stations
    $userStmtQuery = $pdo->prepare(
        'SELECT pk_serialNumber, name, description FROM station WHERE fk_user_owns = :user ORDER BY pk_serialNumber'
    );
    $userStmtQuery->execute([':user' => $_SESSION['username']]);
    $userStations = $userStmtQuery->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    if (!$dbError) {
        $dbError = 'Database error. Please try again later.';
    }
}

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <title>Stations</title>
</head>
<body>
    <?php include 'assets/header.php'; ?>

    <main class="page">
        <div class="stations-container">
            <!-- Your Stations Section -->
            <section class="card">
                <h2 class="card-title">Your Stations</h2>

                <?php if ($successMsg): ?>
                    <p class="success-text"><?php echo h($successMsg); ?></p>
                <?php endif; ?>

                <?php if ($dbError): ?>
                    <p class="error-text"><?php echo h($dbError); ?></p>
                <?php endif; ?>

                <?php if (empty($userStations)): ?>
                    <p class="muted">You have no stations yet. Register one below.</p>
                <?php else: ?>
                    <div class="stations-list">
                        <?php foreach ($userStations as $station): ?>
                            <div class="station-card">
                                <form method="post" class="station-form">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="station_id" value="<?php echo h($station['pk_serialNumber']); ?>">

                                    <div class="station-field">
                                        <label class="field-label">Serial:</label>
                                        <span class="station-id"><?php echo h($station['pk_serialNumber']); ?></span>
                                    </div>

                                    <div class="station-field">
                                        <label class="field-label" for="name-<?php echo h($station['pk_serialNumber']); ?>">Name:</label>
                                        <input 
                                            id="name-<?php echo h($station['pk_serialNumber']); ?>"
                                            type="text" 
                                            name="name" 
                                            class="input-text" 
                                            value="<?php echo h($station['name'] ?? ''); ?>"
                                            placeholder="Station name"
                                        >
                                    </div>

                                    <div class="station-field">
                                        <label class="field-label" for="desc-<?php echo h($station['pk_serialNumber']); ?>">Description:</label>
                                        <textarea 
                                            id="desc-<?php echo h($station['pk_serialNumber']); ?>"
                                            name="description" 
                                            class="input-textarea" 
                                            placeholder="Station description"
                                        ><?php echo h($station['description'] ?? ''); ?></textarea>
                                    </div>

                                    <button type="submit" class="primary-btn">Save</button>
                                </form>
                                <form method="post" onsubmit="return confirm('Delete this station and its measurements? This cannot be undone.');" style="margin-top:8px;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="station_id" value="<?php echo h($station['pk_serialNumber']); ?>">
                                    <button type="submit" class="danger-btn">Delete Station</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Register New Station Section -->
            <section class="card">
                <h2 class="card-title">Register New Station</h2>

                <form method="post" class="register-form">
                    <input type="hidden" name="action" value="register">

                    <div class="form-group">
                        <label class="field-label" for="station-serial">Serial Number:</label>
                        <input 
                            id="station-serial" 
                            type="text" 
                            name="station_serial" 
                            class="input-text" 
                            placeholder="Enter station serial number (e.g., SN-1001)"
                            required
                        >
                    </div>

                    <button type="submit" class="primary-btn">Register Station</button>
                </form>
            </section>
        </div>
    </main>

    <?php include 'assets/footer.php'; ?>
</body>
</html>