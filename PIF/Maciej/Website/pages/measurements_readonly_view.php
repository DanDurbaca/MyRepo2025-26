<?php
// pages/measurements_readonly_view.php
// Read-only view for measurements

$collection         = $view_data['collection'];
$measurements       = $view_data['measurements'];
$page               = $view_data['page'];
$total_pages        = $view_data['total_pages'];
$is_creator         = $view_data['is_creator'];
$total_measurements = $view_data['total_measurements'];
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="container">

    <!-- Back link -->
    <a href="<?= BASE_URL ?>/controller/<?= $is_creator ? 'collections.php' : 'shared.php' ?>"
       class="btn btn-secondary btn-sm">
        ← Back to <?= $is_creator ? 'My Collections' : 'Shared Collections' ?>
    </a>

    <!-- Collection info -->
    <section style="margin-top:20px;">
        <h1><?= htmlspecialchars($collection['name']) ?></h1>
        <?php if (!empty($collection['description'])): ?>
            <p><?= htmlspecialchars($collection['description']) ?></p>
        <?php endif; ?>
        <small>
            Created by <?= htmlspecialchars($collection['creator_name']) ?>
            (<?= htmlspecialchars($collection['creator_username']) ?>)
        </small>
    </section>

    <!-- Measurements table -->
    <section style="margin-top:30px;">
        <?php if (empty($measurements)): ?>
            <p>No measurements found in this collection.</p>
        <?php else: ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Station</th>
                            <th>Temp (°C)</th>
                            <th>Humidity (%)</th>
                            <th>Pressure (hPa)</th>
                            <th>Light (lux)</th>
                            <th>Gas (ppm)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Loop through measurements and display each of them -->
                        <?php foreach ($measurements as $i => $m): ?>
                            <tr>
                                <td><?= (($page - 1) * 25) + $i + 1 ?></td>
                                <td><?= date('Y-m-d', strtotime($m['timestamp'])) ?></td>
                                <td><?= date('H:i:s', strtotime($m['timestamp'])) ?></td>
                                <td><?= htmlspecialchars($m['station_name']) ?></td>
                                <td><?= htmlspecialchars($m['temperature']) ?></td>
                                <td><?= htmlspecialchars($m['humidity']) ?></td>
                                <td><?= htmlspecialchars($m['pressure']) ?></td>
                                <td><?= htmlspecialchars($m['light']) ?></td>
                                <td><?= htmlspecialchars($m['gas']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination links -->
            <?php if ($total_pages > 1): ?>
                <nav style="margin-top:20px;">
                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                        <?php if ($p === $page): ?>
                            <strong><?= $p ?></strong>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/controller/measurements_readonly.php?collection_id=<?= $collection['pk_collection'] ?>&page=<?= $p ?>">
                                <?= $p ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>

            <p style="margin-top:10px;">
                Showing <?= count($measurements) ?> of <?= $total_measurements ?> measurements
            </p>

        <?php endif; ?>
    </section>

</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>