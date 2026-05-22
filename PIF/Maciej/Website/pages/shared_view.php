<?php
// pages/shared_view.php
// View for collections that friends have shared with the user

// Extract data passed from controller
$shared_collections = $view_data['shared_collections'] ?? []; // Array of collections shared by friends
$success_message    = $view_data['success_message'] ?? '';    // Feedback for successful actions
$error_message      = $view_data['error_message'] ?? '';      // Feedback for errors
?>

<?php include __DIR__ . '/../includes/header.php'; ?> <!-- Include site header and navigation -->

<main class="container"> <!-- Main content wrapper -->

    <!-- Page heading -->
    <h1>Shared with Me</h1>
    <p>Collections that your friends have shared with you.</p>

    <!-- Display success feedback -->
    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success_message) ?> <!-- Escaped to prevent XSS -->
        </div>
    <?php endif; ?>

    <!-- Display error feedback -->
    <?php if ($error_message): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <!-- Shared collections -->
    <?php if ($shared_collections): ?>
        <div class="grid grid-2"> <!-- CSS grid: 2 cards per row -->
            <?php foreach ($shared_collections as $c): ?>
                <div class="shared-card"> <!-- Individual shared collection -->

                    <!-- Collection header -->
                    <div class="shared-header">
                        <div class="shared-title"><?= htmlspecialchars($c['name']) ?></div> <!-- Collection name -->
                        <div class="shared-by">
                            Shared by <?= htmlspecialchars($c['firstName'] . ' ' . $c['lastName']) ?>
                            (@<?= htmlspecialchars($c['creator_username']) ?>)
                        </div>
                    </div>

                    <!-- Collection details -->
                    <div class="shared-info">
                        <?php if (!empty($c['station_name'])): ?>
                            <div><strong>Station:</strong> <?= htmlspecialchars($c['station_name']) ?></div>
                        <?php endif; ?>

                        <div><strong>Measurements:</strong> <?= (int)$c['measurement_count'] ?></div>

                        <?php if (!empty($c['description'])): ?>
                            <div><strong>Description:</strong> <?= htmlspecialchars($c['description']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Remove shared collection form -->
                    <form method="post" onsubmit="return confirm('Remove this collection from your shared list?');">
                        <input type="hidden" name="action" value="unshare_collection"> <!-- Tell controller the action -->
                        <input type="hidden" name="collection_id" value="<?= (int)$c['pk_collection'] ?>"> <!-- Collection ID -->
                        <button type="submit" class="btn btn-danger btn-sm remove-btn">Remove</button>
                    </form>

                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- Empty state when no shared collections exist -->
        <div class="empty-state">
            <h3>No shared collections</h3>
            <p>Ask your friends to share their collections with you.</p>
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?> <!-- Include site footer -->