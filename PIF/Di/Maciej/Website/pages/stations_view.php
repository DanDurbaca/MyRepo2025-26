<?php 
//pages/stations_view.php
// View for managing user stations
include __DIR__ . '/../includes/header.php'; ?> <!-- Include the site header and navigation -->

<div class="container">

    <h1>My Stations</h1> <!-- Page main heading -->

    <!-- Display success message if set -->
    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success_message) ?> <!-- Output safe success text -->
        </div>
    <?php endif; ?>

    <!-- Display error message if set -->
    <?php if ($error_message): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error_message) ?> <!-- Output safe error text -->
        </div>
    <?php endif; ?>

    <!-- Register New Station Section -->
    <div class="card">
        <h2 class="card-title">Register New Station</h2> <!-- Card heading -->

        <?php if ($available_stations): ?> <!-- Check if there are unregistered stations available -->
            <form method="post" class="mt-2"> <!-- POST form to register a station -->
                <input type="hidden" name="action" value="register_station"> <!-- Hidden input specifies controller action -->

                <div class="form-row">
                    <div class="form-group">
                        <label>Serial Number</label>
                        <select name="serial_number" required> <!-- Dropdown to select station serial number -->
                            <option value="">Select serial</option>
                            <?php foreach ($available_stations as $s): ?> <!-- Loop through available stations -->
                                <option value="<?= $s['pk_serialNumber'] ?>">
                                    <?= $s['pk_serialNumber'] ?> <!-- Show serial number as option -->
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Station name</label>
                        <input name="station_name" required> <!-- Input for station name -->
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"></textarea> <!-- Optional description -->
                </div>

                <button type="submit" class="btn btn-primary">
                    Register Station <!-- Submit button -->
                </button>
            </form>
        <?php else: ?>
            <p class="text-muted">No available stations to register.</p> <!-- Info if no stations are available -->
        <?php endif; ?>
    </div>

    <!-- User Stations Section -->
    <h2 class="mt-3">Your Stations</h2> <!-- Heading for existing stations -->

    <?php if ($stations): ?> <!-- Check if user has registered stations -->
        <div class="grid grid-2"> <!-- Display stations in a 2-column grid -->
            <?php foreach ($stations as $station): ?> <!-- Loop through user stations -->
                <div class="card station-card">
                    <form method="post"> <!-- Form to update station info -->
                        <input type="hidden" name="action" value="update_station"> <!-- Hidden action for controller -->
                        <input type="hidden" name="serial_number" value="<?= $station['pk_serialNumber'] ?>"> <!-- Station identifier -->

                        <h3 class="card-title">
                            <?= htmlspecialchars($station['pk_serialNumber']) ?> <!-- Display station serial -->
                        </h3>

                        <div class="form-group">
                            <label>Name</label>
                            <input name="station_name"
                                   value="<?= htmlspecialchars($station['name']) ?>" required> <!-- Editable station name -->
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description"><?= htmlspecialchars($station['description']) ?></textarea> <!-- Editable description -->
                        </div>

                        <button type="submit" class="btn btn-secondary">
                            Save Changes <!-- Submit updates -->
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?> <!-- No stations yet -->
        <div class="empty-state">
            <h3>No stations yet</h3>
            <p>Register your first station above.</p>
        </div>
    <?php endif; ?>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?> <!-- Include site footer -->