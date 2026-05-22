<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$pageTitle = 'Collections';
$message = '';

// Handle collection operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_collection') {
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        $stationId = sanitize($_POST['station_id']);
        $startDate = $_POST['start_date'] . ' 00:00:00';
        $endDate = $_POST['end_date'] . ' 23:59:59';
        
        // Verify station belongs to user (or admin can use any station)
        $stmt = $pdo->prepare("SELECT pk_serialNumber FROM station WHERE pk_serialNumber = ?");
        $stmt->execute([$stationId]);
        if (!$stmt->fetch()) {
            $message = 'Invalid station selected.';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO collection (name, description, fk_user_creates) 
                VALUES (?, ?, ?)
            ");
            if ($stmt->execute([$name, $description, $_SESSION['username']])) {
                $collectionId = $pdo->lastInsertId();
                
                // Get measurements for the selected station and date range
                $stmt = $pdo->prepare("
                    SELECT pk_measurement 
                    FROM measurement 
                    WHERE fk_station_records = ? 
                    AND timestamp BETWEEN ? AND ?
                ");
                $stmt->execute([$stationId, $startDate, $endDate]);
                $measurements = $stmt->fetchAll();
                
                // Add measurements to collection
                $insertStmt = $pdo->prepare("INSERT INTO contains (pkfk_collection, pkfk_measurement) VALUES (?, ?)");
                foreach ($measurements as $measurement) {
                    $insertStmt->execute([$collectionId, $measurement['pk_measurement']]);
                }
                
                $message = "Collection created with " . count($measurements) . " measurements!";
            } else {
                $message = "Failed to create collection.";
            }
        }
    }
    
    elseif ($action === 'rename_collection') {
        $collectionId = (int)$_POST['collection_id'];
        $newName = sanitize($_POST['new_name']);
        
        // Check ownership
        $stmt = $pdo->prepare("SELECT fk_user_creates FROM collection WHERE pk_collection = ?");
        $stmt->execute([$collectionId]);
        $collection = $stmt->fetch();
        
        if (!$collection) {
            $message = "Collection not found.";
        } elseif (!isAdmin() && $collection['fk_user_creates'] !== $_SESSION['username']) {
            $message = "You don't have permission to rename this collection.";
        } else {
            $stmt = $pdo->prepare("UPDATE collection SET name = ? WHERE pk_collection = ?");
            if ($stmt->execute([$newName, $collectionId])) {
                $message = "Collection renamed successfully!";
            } else {
                $message = "Failed to rename collection.";
            }
        }
    }
    
    elseif ($action === 'delete_collection') {
        $collectionId = (int)$_POST['collection_id'];
        
        // Check ownership
        $stmt = $pdo->prepare("SELECT fk_user_creates FROM collection WHERE pk_collection = ?");
        $stmt->execute([$collectionId]);
        $collection = $stmt->fetch();
        
        if (!$collection) {
            $message = "Collection not found.";
        } elseif (!isAdmin() && $collection['fk_user_creates'] !== $_SESSION['username']) {
            $message = "You don't have permission to delete this collection.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM collection WHERE pk_collection = ?");
            if ($stmt->execute([$collectionId])) {
                $message = "Collection deleted successfully!";
            } else {
                $message = "Failed to delete collection.";
            }
        }
    }
    
    elseif ($action === 'share_collection') {
        $collectionId = (int)$_POST['collection_id'];
        $friendUsername = sanitize($_POST['friend_username']);
        
        // Check if friend exists and is actually a friend
        $stmt = $pdo->prepare("
            SELECT 1 FROM isfriend 
            WHERE (pkfk_user_user = ? AND pkfk_user_friend = ?)
            OR (pkfk_user_user = ? AND pkfk_user_friend = ?)
        ");
        $stmt->execute([$_SESSION['username'], $friendUsername, $friendUsername, $_SESSION['username']]);
        
        if (!$stmt->fetch()) {
            $message = "User is not your friend.";
        } else {
            // Check if already shared
            $stmt = $pdo->prepare("SELECT 1 FROM hasaccess WHERE pkfk_user = ? AND pkfk_collection = ?");
            $stmt->execute([$friendUsername, $collectionId]);
            
            if ($stmt->fetch()) {
                $message = "Collection already shared with this user.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO hasaccess (pkfk_user, pkfk_collection) VALUES (?, ?)");
                if ($stmt->execute([$friendUsername, $collectionId])) {
                    $message = "Collection shared successfully!";
                } else {
                    $message = "Failed to share collection.";
                }
            }
        }
    }
    
    elseif ($action === 'unshare_collection') {
        $collectionId = (int)$_POST['collection_id'];
        $friendUsername = sanitize($_POST['friend_username']);
        
        $stmt = $pdo->prepare("DELETE FROM hasaccess WHERE pkfk_user = ? AND pkfk_collection = ?");
        if ($stmt->execute([$friendUsername, $collectionId])) {
            $message = "Collection unshared successfully!";
        } else {
            $message = "Failed to unshare collection.";
        }
    }
}

// Get user's collections
$stmt = $pdo->prepare("
    SELECT c.*, 
           COUNT(con.pkfk_measurement) as measurement_count
    FROM collection c
    LEFT JOIN contains con ON c.pk_collection = con.pkfk_collection
    WHERE c.fk_user_creates = ?
    GROUP BY c.pk_collection
    ORDER BY c.pk_collection DESC
");
$stmt->execute([$_SESSION['username']]);
$myCollections = $stmt->fetchAll();

// Get collections shared with user
$stmt = $pdo->prepare("
    SELECT c.*, u.firstName, u.lastName
    FROM collection c
    JOIN hasaccess h ON c.pk_collection = h.pkfk_collection
    JOIN user u ON c.fk_user_creates = u.pk_username
    WHERE h.pkfk_user = ?
    ORDER BY c.pk_collection DESC
");
$stmt->execute([$_SESSION['username']]);
$sharedCollections = $stmt->fetchAll();

// Get station names for each collection (separate query)
foreach ($myCollections as &$collection) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT s.name
        FROM measurement m
        JOIN contains con ON m.pk_measurement = con.pkfk_measurement
        JOIN station s ON m.fk_station_records = s.pk_serialNumber
        WHERE con.pkfk_collection = ?
    ");
    $stmt->execute([$collection['pk_collection']]);
    $stations = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $collection['station_names'] = implode(', ', $stations);
}
unset($collection); // Unset reference

// Get user's stations for collection creation
if (isAdmin()) {
    $stmt = $pdo->query("SELECT pk_serialNumber, name FROM station ORDER BY name");
} else {
    $stmt = $pdo->prepare("SELECT pk_serialNumber, name FROM station WHERE fk_user_owns = ? ORDER BY name");
    $stmt->execute([$_SESSION['username']]);
}
$myStations = $stmt->fetchAll();

// Get user's friends for sharing
$stmt = $pdo->prepare("
    SELECT u.pk_username, u.firstName, u.lastName 
    FROM user u 
    WHERE u.pk_username IN (
        SELECT pkfk_user_friend FROM isfriend WHERE pkfk_user_user = ?
        UNION
        SELECT pkfk_user_user FROM isfriend WHERE pkfk_user_friend = ?
    )
    ORDER BY u.firstName, u.lastName
");
$stmt->execute([$_SESSION['username'], $_SESSION['username']]);
$myFriends = $stmt->fetchAll();

$pageJS = 'collections.js';
?>
<?php include 'includes/header.php'; ?>

<div class="main-content">
    <nav class="navbar navbar-light bg-white rounded mb-4">
        <div class="container-fluid">
            <h2 class="navbar-brand mb-0">Collections</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="bi bi-plus-circle"></i> Create Collection
            </button>
        </div>
    </nav>
    
    <?php if ($message): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- My Collections -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">My Collections</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Stations</th>
                            <th>Measurements</th>
                            <th>Shared With</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($myCollections)): ?>
                            <tr>
                                <td colspan="6" class="text-center">You haven't created any collections yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($myCollections as $collection): ?>
                                <?php
                                // Get who this collection is shared with
                                $stmt = $pdo->prepare("
                                    SELECT u.pk_username, u.firstName, u.lastName 
                                    FROM hasaccess h
                                    JOIN user u ON h.pkfk_user = u.pk_username
                                    WHERE h.pkfk_collection = ?
                                ");
                                $stmt->execute([$collection['pk_collection']]);
                                $sharedWith = $stmt->fetchAll();
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($collection['name']); ?></strong></td>
                                    <td><?php echo nl2br(htmlspecialchars($collection['description'])); ?></td>
                                    <td><?php echo htmlspecialchars($collection['station_names']); ?></td>
                                    <td><span class="badge bg-primary"><?php echo $collection['measurement_count']; ?></span></td>
                                    <td>
                                        <?php foreach ($sharedWith as $user): ?>
                                            <span class="badge bg-success me-1">
                                                <?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?>
                                                <button class="btn-close btn-close-white btn-sm ms-1" 
                                                        onclick="unshareCollection(<?php echo $collection['pk_collection']; ?>, '<?php echo $user['pk_username']; ?>')"></button>
                                            </span>
                                        <?php endforeach; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" 
                                                    onclick="renameCollection(<?php echo $collection['pk_collection']; ?>, '<?php echo htmlspecialchars($collection['name']); ?>')">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-outline-success" 
                                                    onclick="shareCollection(<?php echo $collection['pk_collection']; ?>)">
                                                <i class="bi bi-share"></i>
                                            </button>
                                            <button class="btn btn-outline-info" 
                                                    onclick="viewCollection(<?php echo $collection['pk_collection']; ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" 
                                                    onclick="confirmDelete(<?php echo $collection['pk_collection']; ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Shared With Me -->
    <?php if (!empty($sharedCollections)): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Shared With Me</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($sharedCollections as $collection): ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><?php echo htmlspecialchars($collection['name']); ?></h6>
                        </div>
                        <div class="card-body">
                            <p><strong>Shared by:</strong> <?php echo htmlspecialchars($collection['firstName'] . ' ' . $collection['lastName']); ?></p>
                            <p><?php echo nl2br(htmlspecialchars($collection['description'])); ?></p>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-sm btn-outline-primary" 
                                    onclick="viewCollection(<?php echo $collection['pk_collection']; ?>)">
                                <i class="bi bi-eye"></i> View
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Create Collection Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="create_collection">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title">Create New Collection</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Collection Name *</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Station *</label>
                            <select class="form-select" name="station_id" required>
                                <option value="">Select Station</option>
                                <?php foreach ($myStations as $station): ?>
                                    <option value="<?php echo $station['pk_serialNumber']; ?>">
                                        <?php echo htmlspecialchars($station['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date *</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date *</label>
                            <input type="date" class="form-control" name="end_date" required>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        The collection will include all measurements from the selected station within the date range.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Collection</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rename Modal -->
<div class="modal fade" id="renameModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="rename_collection">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="collection_id" id="renameCollectionId">
                
                <div class="modal-header">
                    <h5 class="modal-title">Rename Collection</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">New Name</label>
                        <input type="text" class="form-control" name="new_name" id="renameCollectionName" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Rename</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Share Modal -->
<div class="modal fade" id="shareModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="share_collection">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="collection_id" id="shareCollectionId">
                
                <div class="modal-header">
                    <h5 class="modal-title">Share Collection</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Friend</label>
                        <select class="form-select" name="friend_username" required>
                            <option value="">Select Friend</option>
                            <?php foreach ($myFriends as $friend): ?>
                                <option value="<?php echo $friend['pk_username']; ?>">
                                    <?php echo htmlspecialchars($friend['firstName'] . ' ' . $friend['lastName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        The selected friend will be able to view all measurements in this collection.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Share</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="delete_collection">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="collection_id" id="deleteCollectionId">
                
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Delete Collection</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this collection? All shared access will be removed.</p>
                    <p class="text-danger"><strong>This action cannot be undone!</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Collection Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Collection Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="collectionDetails">
                <!-- Details loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>