<?php
require_once 'config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('dashboard.php');
}

$pageTitle = 'Admin Panel';
$message = '';
$activeTab = $_GET['tab'] ?? 'users';

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_user') {
        $username = sanitize($_POST['username']);
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        $firstName = sanitize($_POST['first_name']);
        $lastName = sanitize($_POST['last_name']);
        $role = sanitize($_POST['role']);
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO user (pk_username, email, password, firstName, lastName, role) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$username, $email, $hashedPassword, $firstName, $lastName, $role])) {
            $message = "User created successfully!";
        } else {
            $message = "Failed to create user.";
        }
    }
    
    elseif ($action === 'update_user') {
        $username = sanitize($_POST['username']);
        $email = sanitize($_POST['email']);
        $firstName = sanitize($_POST['first_name']);
        $lastName = sanitize($_POST['last_name']);
        $role = sanitize($_POST['role']);
        
        $stmt = $pdo->prepare("
            UPDATE user 
            SET email = ?, firstName = ?, lastName = ?, role = ? 
            WHERE pk_username = ?
        ");
        
        if ($stmt->execute([$email, $firstName, $lastName, $role, $username])) {
            $message = "User updated successfully!";
        } else {
            $message = "Failed to update user.";
        }
    }
    
    elseif ($action === 'delete_user') {
        $username = sanitize($_POST['username']);
        
        if ($username === $_SESSION['username']) {
            $message = "You cannot delete your own account!";
        } else {
            $stmt = $pdo->prepare("DELETE FROM user WHERE pk_username = ?");
            if ($stmt->execute([$username])) {
                $message = "User deleted successfully!";
            } else {
                $message = "Failed to delete user.";
            }
        }
    }
    
    elseif ($action === 'create_station') {
        $serialNumber = sanitize($_POST['serial_number']);
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        $owner = $_POST['owner'] ?: null;
        
        $stmt = $pdo->prepare("
            INSERT INTO station (pk_serialNumber, name, description, fk_user_owns) 
            VALUES (?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$serialNumber, $name, $description, $owner])) {
            $message = "Station created successfully!";
        } else {
            $message = "Failed to create station.";
        }
    }
    
    elseif ($action === 'update_station') {
        $serialNumber = sanitize($_POST['serial_number']);
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        $owner = $_POST['owner'] ?: null;
        
        $stmt = $pdo->prepare("
            UPDATE station 
            SET name = ?, description = ?, fk_user_owns = ? 
            WHERE pk_serialNumber = ?
        ");
        
        if ($stmt->execute([$name, $description, $owner, $serialNumber])) {
            $message = "Station updated successfully!";
        } else {
            $message = "Failed to update station.";
        }
    }
    
    elseif ($action === 'delete_station') {
        $serialNumber = sanitize($_POST['serial_number']);
        
        $stmt = $pdo->prepare("DELETE FROM station WHERE pk_serialNumber = ?");
        if ($stmt->execute([$serialNumber])) {
            $message = "Station deleted successfully!";
        } else {
            $message = "Failed to delete station.";
        }
    }
}

// Get data based on active tab
switch ($activeTab) {
    case 'stations':
    $stmt = $pdo->query("
        SELECT s.*, u.firstName, u.lastName 
        FROM station s 
        LEFT JOIN user u ON s.fk_user_owns = u.pk_username 
        WHERE s.name IS NOT NULL
        ORDER BY s.name
    ");
    $data = $stmt->fetchAll();
    break;

    case 'users':
        $stmt = $pdo->query("SELECT * FROM user ORDER BY pk_username");
        $data = $stmt->fetchAll();
        break;
        
    case 'stations':
        $stmt = $pdo->query("
            SELECT s.*, u.firstName, u.lastName 
            FROM station s 
            LEFT JOIN user u ON s.fk_user_owns = u.pk_username 
            ORDER BY s.name
        ");
        $data = $stmt->fetchAll();
        break;
        
    case 'measurements':
        $stationFilter = $_GET['station'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        
        $whereConditions = [];
        $params = [];
        
        if ($stationFilter) {
            $whereConditions[] = "m.fk_station_records = ?";
            $params[] = $stationFilter;
        }
        
        if ($dateFrom) {
            $whereConditions[] = "m.timestamp >= ?";
            $params[] = $dateFrom . ' 00:00:00';
        }
        
        if ($dateTo) {
            $whereConditions[] = "m.timestamp <= ?";
            $params[] = $dateTo . ' 23:59:59';
        }
        
        $query = "
            SELECT m.*, s.name as station_name, u.pk_username as owner 
            FROM measurement m
            JOIN station s ON m.fk_station_records = s.pk_serialNumber
            LEFT JOIN user u ON s.fk_user_owns = u.pk_username
        ";
        
        if (!empty($whereConditions)) {
            $query .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        $query .= " ORDER BY m.timestamp DESC LIMIT 100";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        
        // Get stations for filter
        $stmt = $pdo->query("SELECT pk_serialNumber, name FROM station WHERE name IS NOT NULL ORDER BY name");
        $stations = $stmt->fetchAll();
        break;

        
    case 'collections':
        $stmt = $pdo->query("
            SELECT c.*, u.firstName, u.lastName 
            FROM collection c 
            JOIN user u ON c.fk_user_creates = u.pk_username 
            ORDER BY c.pk_collection DESC
        ");
        $data = $stmt->fetchAll();
        break;
        
    case 'friendships':
        $stmt = $pdo->query("
            SELECT f.*, 
                   u1.firstName as user1_first, u1.lastName as user1_last,
                   u2.firstName as user2_first, u2.lastName as user2_last
            FROM isfriend f
            JOIN user u1 ON f.pkfk_user_user = u1.pk_username
            JOIN user u2 ON f.pkfk_user_friend = u2.pk_username
            ORDER BY f.pkfk_user_user
        ");
        $data = $stmt->fetchAll();
        break;
        
    default:
        $activeTab = 'users';
        $stmt = $pdo->query("SELECT * FROM user ORDER BY pk_username");
        $data = $stmt->fetchAll();
}

// Get all users for dropdowns
$stmt = $pdo->query("SELECT pk_username, firstName, lastName FROM user ORDER BY firstName");
$allUsers = $stmt->fetchAll();

$pageJS = 'admin.js';
?>
<?php include 'includes/header.php'; ?>

<div class="main-content">
    <nav class="navbar navbar-light bg-white rounded mb-4">
        <div class="container-fluid">
            <h2 class="navbar-brand mb-0">
                <i class="bi bi-shield-check"></i> Admin Panel
            </h2>
            <span class="text-warning">
                <i class="bi bi-exclamation-triangle"></i> Administrator Access
            </span>
        </div>
    </nav>
    
    <?php if ($message): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Admin Navigation Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab === 'users' ? 'active' : ''; ?>" 
               href="?tab=users">Users</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab === 'stations' ? 'active' : ''; ?>" 
               href="?tab=stations">Stations</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab === 'measurements' ? 'active' : ''; ?>" 
               href="?tab=measurements">Measurements</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab === 'collections' ? 'active' : ''; ?>" 
               href="?tab=collections">Collections</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab === 'friendships' ? 'active' : ''; ?>" 
               href="?tab=friendships">Friendships</a>
        </li>
    </ul>
    
    <!-- Users Tab -->
    <?php if ($activeTab === 'users'): ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>User Management</h4>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="bi bi-person-plus"></i> Create User
            </button>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Stations</th>
                        <th>Collections</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $user): ?>
                        <?php
                        // Get user statistics
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM station WHERE fk_user_owns = ?");
                        $stmt->execute([$user['pk_username']]);
                        $stationCount = $stmt->fetchColumn();
                        
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM collection WHERE fk_user_creates = ?");
                        $stmt->execute([$user['pk_username']]);
                        $collectionCount = $stmt->fetchColumn();
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($user['pk_username']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge <?php echo $user['role'] === 'Admin' ? 'badge-admin' : 'badge-user'; ?>">
                                    <?php echo $user['role']; ?>
                                </span>
                            </td>
                            <td><?php echo $stationCount; ?></td>
                            <td><?php echo $collectionCount; ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" 
                                            onclick="editUser('<?php echo htmlspecialchars($user['pk_username']); ?>', 
                                                             '<?php echo htmlspecialchars($user['email']); ?>',
                                                             '<?php echo htmlspecialchars($user['firstName']); ?>',
                                                             '<?php echo htmlspecialchars($user['lastName']); ?>',
                                                             '<?php echo $user['role']; ?>')">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <?php if ($user['pk_username'] !== $_SESSION['username']): ?>
                                        <button class="btn btn-outline-danger" 
                                                onclick="deleteUser('<?php echo htmlspecialchars($user['pk_username']); ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <!-- Stations Tab -->
    <?php if ($activeTab === 'stations'): ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Station Management</h4>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createStationModal">
                <i class="bi bi-wifi"></i> Create Station
            </button>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Serial Number</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Owner</th>
                        <th>Measurements</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $station): ?>
                        <?php
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM measurement WHERE fk_station_records = ?");
                        $stmt->execute([$station['pk_serialNumber']]);
                        $measurementCount = $stmt->fetchColumn();
                        ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($station['pk_serialNumber']); ?></code></td>
                            <td><?php echo htmlspecialchars($station['name']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($station['description'])); ?></td>
                            <td>
                                <?php if ($station['fk_user_owns']): ?>
                                    <?php echo htmlspecialchars($station['firstName'] . ' ' . $station['lastName']); ?>
                                <?php else: ?>
                                    <span class="text-muted">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $measurementCount; ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" 
                                            onclick="editStation('<?php echo htmlspecialchars($station['pk_serialNumber']); ?>',
                                                                 '<?php echo htmlspecialchars($station['name']); ?>',
                                                                 '<?php echo htmlspecialchars($station['description']); ?>',
                                                                 '<?php echo htmlspecialchars($station['fk_user_owns']); ?>')">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="measurements.php?station=<?php echo urlencode($station['pk_serialNumber']); ?>" 
                                       class="btn btn-outline-info">
                                        <i class="bi bi-graph-up"></i>
                                    </a>
                                    <button class="btn btn-outline-danger" 
                                            onclick="deleteStation('<?php echo htmlspecialchars($station['pk_serialNumber']); ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <!-- Measurements Tab -->
    <?php if ($activeTab === 'measurements'): ?>
        <div class="filter-section mb-4">
            <h4>Measurement Management</h4>
            <form method="GET" class="row g-3 mt-2">
                <input type="hidden" name="tab" value="measurements">
                
                <div class="col-md-4">
                    <label class="form-label">Station</label>
                    <select name="station" class="form-select">
                        <option value="">All Stations</option>
                        <?php foreach ($stations as $station): ?>
                            <option value="<?php echo $station['pk_serialNumber']; ?>" 
                                <?php echo ($stationFilter == $station['pk_serialNumber']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($station['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo $dateFrom; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo $dateTo; ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="d-grid gap-2 w-100">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="?tab=measurements" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Station</th>
                        <th>Owner</th>
                        <th>Temperature</th>
                        <th>Humidity</th>
                        <th>Pressure</th>
                        <th>Light</th>
                        <th>Gas</th>
                        <th>Timestamp</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $measurement): ?>
                        <tr>
                            <td><?php echo $measurement['pk_measurement']; ?></td>
                            <td><?php echo htmlspecialchars($measurement['station_name']); ?></td>
                            <td><?php echo htmlspecialchars($measurement['owner']); ?></td>
                            <td><?php echo $measurement['temperature']; ?></td>
                            <td><?php echo $measurement['humidity']; ?></td>
                            <td><?php echo $measurement['pressure']; ?></td>
                            <td><?php echo $measurement['light']; ?></td>
                            <td><?php echo $measurement['gas']; ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($measurement['timestamp'])); ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-danger" 
                                        onclick="deleteMeasurement(<?php echo $measurement['pk_measurement']; ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <!-- Collections Tab -->
    <?php if ($activeTab === 'collections'): ?>
        <h4>Collection Management</h4>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Created By</th>
                        <th>Shared With</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $collection): ?>
                        <?php
                        // Get shared users count
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM hasaccess WHERE pkfk_collection = ?");
                        $stmt->execute([$collection['pk_collection']]);
                        $sharedCount = $stmt->fetchColumn();
                        ?>
                        <tr>
                            <td><?php echo $collection['pk_collection']; ?></td>
                            <td><?php echo htmlspecialchars($collection['name']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($collection['description'])); ?></td>
                            <td><?php echo htmlspecialchars($collection['firstName'] . ' ' . $collection['lastName']); ?></td>
                            <td>
                                <?php if ($sharedCount > 0): ?>
                                    <span class="badge bg-success"><?php echo $sharedCount; ?> users</span>
                                <?php else: ?>
                                    <span class="text-muted">Not shared</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="collections.php?view=<?php echo $collection['pk_collection']; ?>" 
                                       class="btn btn-outline-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button class="btn btn-outline-danger" 
                                            onclick="deleteCollection(<?php echo $collection['pk_collection']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <!-- Friendships Tab -->
    <?php if ($activeTab === 'friendships'): ?>
        <h4>Friendship Management</h4>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>User 1</th>
                        <th>User 2</th>
                        <th>Shared Collections</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $friendship): ?>
                        <?php
                        // Count shared collections between users
                        $stmt = $pdo->prepare("
                            SELECT COUNT(DISTINCT h.pkfk_collection) 
                            FROM hasaccess h
                            JOIN collection c ON h.pkfk_collection = c.pk_collection
                            WHERE (h.pkfk_user = ? AND c.fk_user_creates = ?)
                            OR (h.pkfk_user = ? AND c.fk_user_creates = ?)
                        ");
                        $stmt->execute([
                            $friendship['pkfk_user_user'],
                            $friendship['pkfk_user_friend'],
                            $friendship['pkfk_user_friend'],
                            $friendship['pkfk_user_user']
                        ]);
                        $sharedCollections = $stmt->fetchColumn();
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($friendship['user1_first'] . ' ' . $friendship['user1_last']); ?></td>
                            <td><?php echo htmlspecialchars($friendship['user2_first'] . ' ' . $friendship['user2_last']); ?></td>
                            <td><?php echo $sharedCollections; ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-danger" 
                                        onclick="removeFriendship('<?php echo $friendship['pkfk_user_user']; ?>', 
                                                                 '<?php echo $friendship['pkfk_user_friend']; ?>')">
                                    <i class="bi bi-person-dash"></i> Remove
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="create_user">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title">Create New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username *</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role">
                            <option value="User">User</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="username" id="editUserUsername">
                
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" id="editUserEmail" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" id="editUserFirstName">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" id="editUserLastName">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" id="editUserRole">
                            <option value="User">User</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Station Modal -->
<div class="modal fade" id="createStationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="create_station">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title">Create New Station</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Serial Number *</label>
                        <input type="text" class="form-control" name="serial_number" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Owner (optional)</label>
                        <select class="form-select" name="owner">
                            <option value="">Unassigned</option>
                            <?php foreach ($allUsers as $user): ?>
                                <option value="<?php echo $user['pk_username']; ?>">
                                    <?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Create Station</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Station Modal -->
<div class="modal fade" id="editStationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="update_station">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="serial_number" id="editStationSerial">
                
                <div class="modal-header">
                    <h5 class="modal-title">Edit Station</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" class="form-control" name="name" id="editStationName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="editStationDescription" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Owner (optional)</label>
                        <select class="form-select" name="owner" id="editStationOwner">
                            <option value="">Unassigned</option>
                            <?php foreach ($allUsers as $user): ?>
                                <option value="<?php echo $user['pk_username']; ?>">
                                    <?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Station</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Forms -->
<form method="POST" id="deleteUserForm" style="display: none;">
    <input type="hidden" name="action" value="delete_user">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="username" id="deleteUserUsername">
</form>

<form method="POST" id="deleteStationForm" style="display: none;">
    <input type="hidden" name="action" value="delete_station">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="serial_number" id="deleteStationSerial">
</form>

<form method="POST" id="deleteMeasurementForm" style="display: none;">
    <input type="hidden" name="action" value="delete_measurement">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="measurement_id" id="deleteMeasurementId">
</form>

<form method="POST" id="deleteCollectionForm" style="display: none;">
    <input type="hidden" name="action" value="delete_collection">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="collection_id" id="deleteCollectionId">
</form>

<form method="POST" id="removeFriendshipForm" style="display: none;">
    <input type="hidden" name="action" value="remove_friendship">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="user1" id="removeUser1">
    <input type="hidden" name="user2" id="removeUser2">
</form>

<?php include 'includes/footer.php'; ?>