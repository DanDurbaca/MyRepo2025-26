<?php
require_once 'config.php';
require_once 'auth.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$message = "";

// Handle station operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'register_station') {
        $serialNumber = sanitize($_POST['serial_number']);
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        
        // Check if station already exists
        $stmt = $pdo->prepare("SELECT pk_serialNumber, fk_user_owns FROM station WHERE pk_serialNumber = ?");
        $stmt->execute([$serialNumber]);
        $existingStation = $stmt->fetch();
        
        if ($existingStation) {
            if ($existingStation['fk_user_owns'] !== null) {
                $message = "This station is already assigned to another user.";
            } else {
                // Register existing unassigned station
                $stmt = $pdo->prepare("UPDATE station SET fk_user_owns = ?, name = ?, description = ? WHERE pk_serialNumber = ?");
                if ($stmt->execute([$_SESSION['username'], $name, $description, $serialNumber])) {
                    $message = "Station registered successfully!";
                } else {
                    $message = "Failed to register station.";
                }
            }
        } else {
            // Create brand new station
            $stmt = $pdo->prepare("INSERT INTO station (pk_serialNumber, name, description, fk_user_owns) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$serialNumber, $name, $description, $_SESSION['username']])) {
                $message = "Station created and registered successfully!";
            } else {
                $message = "Failed to create station.";
            }
        }
    }
    
    if ($action === 'update_station') {
        $serialNumber = sanitize($_POST['serial_number']);
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        
        $stmt = $pdo->prepare("UPDATE station SET name = ?, description = ? WHERE pk_serialNumber = ? AND fk_user_owns = ?");
        if ($stmt->execute([$name, $description, $serialNumber, $_SESSION['username']])) {
            $message = "Station updated successfully!";
        } else {
            $message = "Failed to update station.";
        }
    }
}

// Get user's stations
$stmt = $pdo->prepare("SELECT * FROM station WHERE fk_user_owns = ? AND name IS NOT NULL ORDER BY pk_serialNumber");
$stmt->execute([$_SESSION['username']]);
$stations = $stmt->fetchAll();

// Get unassigned stations (for admin)
$unassignedStations = [];
if (isAdmin()) {
    $stmt = $pdo->prepare("SELECT * FROM station WHERE fk_user_owns IS NULL AND name IS NOT NULL");
    $stmt->execute();
    $unassignedStations = $stmt->fetchAll();
}

$pageTitle = 'My Stations';
$pageJS = 'stations.js';
?>
<?php include 'includes/header.php'; ?>

<div class="main-content">
    <nav class="navbar navbar-light bg-white rounded mb-4">
        <div class="container-fluid">
            <h2 class="navbar-brand mb-0">My Stations</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerModal">
                <i class="bi bi-plus-circle"></i> Register New Station
            </button>
        </div>
    </nav>
    
    <?php if ($message): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Stations Grid -->
    <div class="row">
        <?php foreach ($stations as $station): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><?php echo htmlspecialchars($station['name']); ?></h5>
                </div>
                <div class="card-body">
                    <p><strong>Serial:</strong> <?php echo htmlspecialchars($station['pk_serialNumber']); ?></p>
                    <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($station['description'])); ?></p>
                </div>
                <div class="card-footer">
                    <button class="btn btn-sm btn-outline-primary edit-station" 
                            data-serial="<?php echo $station['pk_serialNumber']; ?>"
                            data-name="<?php echo htmlspecialchars($station['name']); ?>"
                            data-description="<?php echo htmlspecialchars($station['description']); ?>">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <a href="measurements.php?station=<?php echo urlencode($station['pk_serialNumber']); ?>" 
                       class="btn btn-sm btn-outline-success">
                        <i class="bi bi-graph-up"></i> View Data
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($stations)): ?>
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="bi bi-info-circle"></i> You don't have any stations registered yet.
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if (isAdmin() && !empty($unassignedStations)): ?>
    <div class="card mt-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Unassigned Stations (Admin View)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Serial Number</th>
                            <th>Name</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($unassignedStations as $station): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($station['pk_serialNumber']); ?></td>
                            <td><?php echo htmlspecialchars($station['name']); ?></td>
                            <td><?php echo htmlspecialchars($station['description']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Register Station Modal -->
<div class="modal fade" id="registerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="register_station">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title">Register New Station</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Serial Number *</label>
                        <input type="text" class="form-control" name="serial_number" required 
                               placeholder="Enter station serial number">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Station Name *</label>
                        <input type="text" class="form-control" name="name" required 
                               placeholder="Give your station a name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" 
                                  placeholder="Describe your station..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Register Station</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Station Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="update_station">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="serial_number" id="editSerial">
                
                <div class="modal-header">
                    <h5 class="modal-title">Edit Station</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Station Name *</label>
                        <input type="text" class="form-control" name="name" id="editName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="editDescription" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>