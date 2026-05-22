<?php 
// admin/stations_view.php
// Admin panel page for managing stations (create, update, delete)

// Include the site header (HTML <head> + navigation)
include __DIR__ . '/../includes/header.php'; 
?>

<div class="container admin-stations">
    <h1>Admin – Stations</h1>

    <!-- Display success message if any -->
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <!-- Display error message if any -->
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <!-- CREATE STATION CARD -->
    <div class="card admin-create-station">
        <h2>Create Station</h2>

        <!-- Form to create a new station -->
        <form method="post" class="create-station-form">
            <!-- Hidden input to indicate the action for the controller -->
            <input type="hidden" name="action" value="create">

            <div class="form-row">
                <!-- Station Serial Number input -->
                <div class="form-group">
                    <label>Serial Number</label>
                    <input name="serial_number" required>
                </div>

                <!-- Station Name input -->
                <div class="form-group">
                    <label>Name</label>
                    <input name="name" required>
                </div>

                <!-- Station Description input -->
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"></textarea>
                </div>
            </div>

            <div class="form-row">
                <!-- Owner dropdown select -->
                <div class="form-group">
                    <label>Owner</label>
                    <select name="owner">
                        <option value="">Unassigned</option>
                        <!-- Populate with users from database -->
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['pk_username'] ?>"><?= $u['pk_username'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Submit button for creating a station -->
            <div class="create-station-actions">
                <button class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>

    <h2 class="mt-3">All Stations</h2>

    <!-- LOOP THROUGH ALL STATIONS -->
    <?php foreach ($stations as $s): ?>
    <div class="card mb-2 admin-station-card">

        <!-- Station header shows serial number -->
        <div class="admin-item-header">
            <?= htmlspecialchars($s['pk_serialNumber']) ?>
        </div>

        <!-- Form to update or delete a station -->
        <form method="post" class="admin-station-form">
            <!-- Hidden input to pass the station identifier -->
            <input type="hidden" name="serial_number" value="<?= $s['pk_serialNumber'] ?>">

            <div class="form-row">
                <!-- Editable fields -->
                <div class="form-group">
                    <label>Name</label>
                    <input name="name" value="<?= htmlspecialchars($s['name']) ?>">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"><?= htmlspecialchars($s['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Owner</label>
                    <select name="owner">
                        <option value="">Unassigned</option>
                        <!-- Populate users and mark current owner as selected -->
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['pk_username'] ?>"
                                <?= $u['pk_username'] === $s['fk_user_owns'] ? 'selected' : '' ?>>
                                <?= $u['pk_username'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Actions: Update or Delete the station -->
            <div class="admin-station-actions">
                <button name="action" value="update" class="btn btn-primary btn-sm">Save</button>
                <button name="action" value="delete"
                        class="btn btn-danger btn-sm"
                        onclick="return confirm('Delete this station?')">
                    Delete
                </button>
            </div>
        </form>

    </div>
    <?php endforeach; ?>

</div>

<?php 
// Include the site footer (closing HTML, scripts)
include __DIR__ . '/../includes/footer.php'; 
?>