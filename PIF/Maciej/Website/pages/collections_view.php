<?php
// pages/collections_view.php
// Collections page view

// Extract view data passed from controller
$username         = $view_data['username'] ?? '';          // Current user
$is_admin         = $view_data['is_admin'] ?? false;       // Admin flag
$collections      = $view_data['collections'] ?? [];       // List of collections
$user_stations    = $view_data['user_stations'] ?? [];     // Stations owned by user
$success_message  = $view_data['success_message'] ?? '';   // Success message
$error_message    = $view_data['error_message'] ?? '';     // Error message
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Collections</title>

    <!-- Main stylesheet -->
    <link rel="stylesheet" href="/PIF/Website/assets/css/style.css">
</head>
<body>

<?php require_once __DIR__ . '/../includes/header.php'; ?> <!-- Header / navigation -->

<main class="container">

    <!-- Page header -->
    <h1>Collections</h1>
    <p>Create and manage collections of measurements from your stations.</p>

    <!-- Display success or error messages -->
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <!-- Section: Create new collection -->
    <section>
        <h2>Create New Collection</h2>

        <?php if ($user_stations): ?>
            <form method="post">
                <input type="hidden" name="action" value="create_collection"> <!-- Identify form action -->

                <label>
                    Collection name
                    <input type="text" name="collection_name" required> <!-- Required collection name -->
                </label>

                <label>
                    Description (optional)
                    <textarea name="description" rows="2"></textarea> <!-- Optional description -->
                </label>

                <label>
                    Station
                    <select name="station_sn" required>
                        <option value="">-- select station --</option>
                        <?php foreach ($user_stations as $s): ?>
                            <option value="<?= htmlspecialchars($s['pk_serialNumber']) ?>">
                                <?= htmlspecialchars($s['name']) ?> <!-- Station name -->
                                (<?= htmlspecialchars($s['pk_serialNumber']) ?>) <!-- Station serial number -->
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Start date & time
                    <input type="datetime-local" name="start_datetime" required>
                </label>

                <label>
                    End date & time
                    <input type="datetime-local" name="end_datetime" required>
                </label>

                <button type="submit" class="btn btn-primary btn-lg">Create Collection</button>
            </form>

        <?php else: ?>
            <p>You have no stations yet. <a href="stations.php">Register a station</a></p>
        <?php endif; ?>
    </section>

    <!-- Section: List existing collections -->
    <section>
        <h2>My Collections</h2>

        <?php if ($collections): ?>
            <div class="collections-table-container">
                <table class="collections-table">

                    <thead>
                        <tr>
                            <th>Name</th>
                            <?php if ($is_admin): ?><th>Creator</th><?php endif; ?> <!-- Admin sees creator -->
                            <th># Measurements</th>
                            <th>Actions</th>
                            <th>Share</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($collections as $c): ?>
                            <tr>

                                <td><?= htmlspecialchars($c['name']) ?></td>

                                <?php if ($is_admin): ?>
                                    <td><?= htmlspecialchars($c['fk_user_creates'] ?? '-') ?></td> <!-- Creator username -->
                                <?php endif; ?>

                                <td><?= (int)$c['measurement_count'] ?></td> <!-- Number of measurements -->

                                <!-- Rename & delete actions -->
                                <td class="actions-cell">
                                    <form method="post" class="inline-form"
                                          onsubmit="return confirm('Delete this collection?');">
                                        <input type="hidden" name="collection_id" value="<?= $c['pk_collection'] ?>"> <!-- Collection ID -->

                                        <input type="text" name="new_name" placeholder="New name" required> <!-- Rename input -->

                                        <button type="submit" name="action" value="rename_collection"
                                                class="btn btn-small btn-primary">Rename
                                        </button>

                                        <button type="submit" name="action" value="delete_collection"
                                                class="btn btn-small btn-danger">Delete
                                        </button>
                                    </form>
                                </td>

                                <!-- Share collection with friends -->
                                <td class="share-cell">
                                    <?php if (!empty($friends)): ?>
                                        <form method="post" class="inline-form">
                                            <input type="hidden" name="action" value="share_collection">
                                            <input type="hidden" name="collection_id" value="<?= $c['pk_collection'] ?>">

                                            <select name="friend_username" required>
                                                <option value="">-- Select friend --</option>
                                                <?php foreach ($friends as $f): ?>
                                                    <option value="<?= htmlspecialchars($f['pk_username']) ?>">
                                                        <?= htmlspecialchars($f['firstName'] . ' ' . $f['lastName'] .
                                                            ' (@' . $f['pk_username'] . ')') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>

                                            <button type="submit" class="btn btn-small btn-primary">Share</button>
                                        </form>
                                    <?php else: ?>
                                        <small>No friends to share with.</small>
                                    <?php endif; ?>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>

        <?php else: ?>
            <p>No collections yet.</p>
        <?php endif; ?>
    </section>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?> <!-- Footer -->

</body>
</html>