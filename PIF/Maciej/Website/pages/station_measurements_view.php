<div class="container">

    <!-- Page heading -->
    <h1>Station Measurements</h1>

    <!-- Error message -->
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error_message) ?> <!-- Errors like "Invalid date" -->
        </div>
    <?php endif; ?>

    <!-- Filter form card -->
    <div class="card">
        <h2 class="card-title">Filter Measurements</h2>
        <form method="get" class="mt-2"> <!-- GET request to allow bookmarking/filtering -->
            <div class="form-row">

                <!-- Station selector -->
                <div class="form-group">
                    <label for="station_sn">Station</label>
                    <select name="station_sn" id="station_sn" required>
                        <option value="">-- Select station --</option>
                        <?php foreach ($stations as $station): ?>
                            <option value="<?= htmlspecialchars($station['pk_serialNumber']) ?>"
                                <?= ($station_sn === $station['pk_serialNumber']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($station['pk_serialNumber'] . ' - ' . ($station['name'] ?? 'Unnamed Station')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Start datetime -->
                <div class="form-group">
                    <label for="start_datetime">Start date & time</label>
                    <input type="datetime-local" name="start_datetime" id="start_datetime"
                           value="<?= !empty($start_datetime) ? date('Y-m-d\TH:i', strtotime($start_datetime)) : '' ?>"
                           required>
                </div>

                <!-- End datetime -->
                <div class="form-group">
                    <label for="end_datetime">End date & time</label>
                    <input type="datetime-local" name="end_datetime" id="end_datetime"
                           value="<?= !empty($end_datetime) ? date('Y-m-d\TH:i', strtotime($end_datetime)) : '' ?>"
                           required>
                </div>

                <!-- Submit button -->
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Show measurements</button>
                </div>

            </div>
        </form>
    </div>

    <!-- Measurement results -->
    <?php if (!empty($station_sn)): ?>
        <div class="card mt-3">
            <h2 class="card-title">
                Results for Station (<?= htmlspecialchars($station_sn) ?> - <?= htmlspecialchars($selected_station_name ?? 'Unnamed Station') ?>)
            </h2>

            <?php if (!empty($measurements)): ?>
                <form method="post" id="measurement-form">

                    <!-- Controls for selection and deletion -->
                    <div class="table-controls mb-2">
                        <button type="button" id="select-all" class="btn btn-sm btn-secondary">Select All</button>
                        <button type="button" id="deselect-all" class="btn btn-sm btn-secondary">Deselect All</button>
                        <span id="counter"><?= count($selected_measurements) ?> selected</span>
                        <button type="submit" name="delete_type" value="selected" class="btn btn-sm btn-danger">Delete Selected</button>
                        <button type="submit" name="delete_type" value="all" class="btn btn-sm btn-danger">Delete All</button>
                    </div>

                    <!-- Measurements table -->
                    <div class="table-container mt-2">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Select</th>
                                    <th>Timestamp</th>
                                    <th>Temperature (°C)</th>
                                    <th>Humidity (%)</th>
                                    <th>Pressure (hPa)</th>
                                    <th>Light (lux)</th>
                                    <th>Gas (ppm)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($measurements as $m): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="row-checkbox" name="selected_measurements[]"
                                                   value="<?= htmlspecialchars($m['pk_measurement']) ?>"
                                                   <?= in_array($m['pk_measurement'], $selected_measurements) ? 'checked' : '' ?>>
                                        </td>
                                        <td><?= htmlspecialchars($m['timestamp']) ?></td>
                                        <td><?= number_format($m['temperature'], 2) ?></td>
                                        <td><?= number_format($m['humidity'], 2) ?></td>
                                        <td><?= number_format($m['pressure'], 2) ?></td>
                                        <td><?= number_format($m['light'], 2) ?></td>
                                        <td><?= number_format($m['gas'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </form>

                <!-- Total measurements -->
                <p class="text-muted text-right"><?= count($measurements) ?> measurement(s) found</p>

                <!-- JS for Select/Deselect and Counter -->
                <script>
                    const selectAllBtn = document.getElementById('select-all');
                    const deselectAllBtn = document.getElementById('deselect-all');
                    const checkboxes = document.querySelectorAll('.row-checkbox');
                    const counter = document.getElementById('counter');

                    function updateCounter() {
                        const selectedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
                        counter.textContent = selectedCount + ' selected';
                    }

                    selectAllBtn.addEventListener('click', () => {
                        checkboxes.forEach(cb => cb.checked = true);
                        updateCounter();
                    });

                    deselectAllBtn.addEventListener('click', () => {
                        checkboxes.forEach(cb => cb.checked = false);
                        updateCounter();
                    });

                    checkboxes.forEach(cb => cb.addEventListener('change', updateCounter));
                </script>

            <?php else: ?>
                <!-- Empty state if no measurements found -->
                <div class="empty-state">
                    <h3>No data available</h3>
                    <p>No measurements found for the selected period.</p>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Empty state if filters not selected yet -->
        <div class="empty-state mt-3">
            <h3>Select filters to begin</h3>
            <p>Please choose a station and date range.</p>
        </div>
    <?php endif; ?>

</div>