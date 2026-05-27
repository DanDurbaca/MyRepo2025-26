<?php
// admin/stations_readonly_view.php
// Includes the global header:
// - starts the session
// - loads navigation
// - applies CSS and theme
include __DIR__ . '/../includes/header.php';
?>

<!-- Main page heading shown to the admin -->
<h1>All Stations (Read-Only)</h1>

<!-- Wrapper used for responsive tables and horizontal scrolling if needed -->
<div class="table-container">

    <!-- Semantic HTML table for displaying structured station data -->
    <table class="table">

        <!-- Table header defining column titles -->
        <thead>
            <tr>
                <th>Serial Number</th>   <!-- Unique identifier of the station -->
                <th>Name</th>            <!-- Human-readable station name -->
                <th>Description</th>     <!-- Optional station description -->
                <th>Owner</th>           <!-- Username of station owner (if assigned) -->
            </tr>
        </thead>

        <!-- Table body populated dynamically from PHP -->
        <tbody>

            <!-- Loop through all stations passed from the controller -->
            <?php foreach ($stations as $s): ?>
            <tr>

                <!-- htmlspecialchars() prevents HTML injection from database content -->
                <td><?= htmlspecialchars($s['pk_serialNumber']) ?></td>

                <!-- Fallback text if station name is NULL -->
                <td><?= htmlspecialchars($s['name'] ?? 'Unnamed') ?></td>

                <!-- Description may be empty but still safely escaped -->
                <td><?= htmlspecialchars($s['description']) ?></td>

                <!-- Shows owner username or "Unassigned" if NULL -->
                <td><?= htmlspecialchars($s['owner_username'] ?? 'Unassigned') ?></td>

            </tr>
            <?php endforeach; ?>

        </tbody>
    </table>
</div>

<?php
// Includes the global footer:
// - closes main layout containers
// - renders footer content
include __DIR__ . '/../includes/footer.php';
?>
