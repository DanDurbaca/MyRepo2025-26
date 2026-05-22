<?php
require_once 'config.php';
require_once 'auth.php';
$pageCSS = 'profile.css';

if (!isLoggedIn()) {
    redirect('index.php');
}

$auth = new Auth($pdo);
$pageTitle = 'Profile';
$message = '';
$success = false;

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $username = sanitize($_POST['username']);
        $email = sanitize($_POST['email']);
        $firstName = sanitize($_POST['first_name']);
        $lastName = sanitize($_POST['last_name']);
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate passwords if changing
        if (!empty($newPassword)) {
            if ($newPassword !== $confirmPassword) {
                $message = "New passwords do not match.";
            } elseif (strlen($newPassword) < 6) {
                $message = "New password must be at least 6 characters long.";
            }
        }
        
        if (!$message) {
            $result = $auth->updateProfile($username, $email, $firstName, $lastName, $currentPassword, $newPassword);
            $message = $result['message'];
            $success = $result['success'];
        }
    }
}

// Get current user data
$stmt = $pdo->prepare("SELECT * FROM user WHERE pk_username = ?");
$stmt->execute([$_SESSION['username']]);
$user = $stmt->fetch();

// Get user statistics
$stats = [
    'stations' => 0,
    'measurements' => 0,
    'collections' => 0,
    'friends' => 0
];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM station WHERE fk_user_owns = ?");
$stmt->execute([$_SESSION['username']]);
$stats['stations'] = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM measurement m
    JOIN station s ON m.fk_station_records = s.pk_serialNumber
    WHERE s.fk_user_owns = ?
");
$stmt->execute([$_SESSION['username']]);
$stats['measurements'] = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM collection WHERE fk_user_creates = ?");
$stmt->execute([$_SESSION['username']]);
$stats['collections'] = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM isfriend 
    WHERE pkfk_user_user = ? OR pkfk_user_friend = ?
");
$stmt->execute([$_SESSION['username'], $_SESSION['username']]);
$stats['friends'] = $stmt->fetchColumn();

$pageJS = 'profile.js';
?>
<?php include 'includes/header.php'; ?>

<div class="main-content">
    <nav class="navbar navbar-light bg-white rounded mb-4">
        <div class="container-fluid">
            <h2 class="navbar-brand mb-0">My Profile</h2>
            <span class="badge <?php echo ($user['role'] === 'Admin') ? 'badge-admin' : 'badge-user'; ?>">
                <?php echo $user['role']; ?>
            </span>
        </div>
    </nav>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Profile Statistics -->
<div class="col-md-3">
    <div class="card text-center mb-4 profile-stats">
        <div class="card-body">
            <div class="mb-3">
                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                     style="width: 80px; height: 80px;">
                    <span class="text-white h4">
                        <?php echo strtoupper(substr($user['firstName'], 0, 1) . substr($user['lastName'], 0, 1)); ?>
                    </span>
                </div>
            </div>
            <h5 class="text-dark"><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></h5>
            <p class="text-muted">@<?php echo htmlspecialchars($user['pk_username']); ?></p>
        </div>
    </div>
    
    <div class="card profile-stats">
        <div class="card-header">
            <h6 class="mb-0 text-dark">Statistics</h6>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="text-dark">Stations</span>
                    <span class="badge bg-primary"><?php echo $stats['stations']; ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="text-dark">Measurements</span>
                    <span class="badge bg-success"><?php echo $stats['measurements']; ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="text-dark">Collections</span>
                    <span class="badge bg-warning"><?php echo $stats['collections']; ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="text-dark">Friends</span>
                    <span class="badge bg-info"><?php echo $stats['friends']; ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mt-4 account-info">
        <div class="card-header">
            <h6 class="mb-0 text-dark">Account Info</h6>
        </div>
        <div class="card-body">
            <p class="text-dark"><strong>Joined:</strong> 
                <?php 
                $joined = new DateTime($user['created_at'] ?? date('Y-m-d H:i:s'));
                echo $joined->format('F j, Y');
                ?>
            </p>
            <p class="text-dark"><strong>Role:</strong> <?php echo $user['role']; ?></p>
            <p class="text-dark"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        </div>
    </div>
</div>
        
        <!-- Profile Form -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Edit Profile</h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="profileForm">
                        <input type="hidden" name="action" value="update_profile">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username *</label>
                                <input type="text" class="form-control" name="username" 
                                       value="<?php echo htmlspecialchars($user['pk_username']); ?>" required>
                                <div class="form-text">Your unique username</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" name="first_name" 
                                       value="<?php echo htmlspecialchars($user['firstName']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" name="last_name" 
                                       value="<?php echo htmlspecialchars($user['lastName']); ?>">
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6>Change Password</h6>
                        <p class="text-muted">Leave blank to keep current password</p>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Current Password *</label>
                                <input type="password" class="form-control" name="current_password" required>
                                <div class="form-text">Required to confirm changes</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="new_password" id="newPassword">
                                <div class="form-text">At least 6 characters</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" name="confirm_password" id="confirmPassword">
                                <div class="invalid-feedback" id="passwordError"></div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            Changing your password will log you out of all other sessions.
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Danger Zone -->
            <div class="card mt-4 border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Danger Zone</h5>
                </div>
                <div class="card-body">
                    <p class="text-danger"><strong>Warning:</strong> These actions are irreversible.</p>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6>Delete Account</h6>
                            <p class="text-muted mb-0">Permanently delete your account and all associated data.</p>
                        </div>
                        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                            <i class="bi bi-trash"></i> Delete Account
                        </button>
                    </div>
                    
                    <?php if (isAdmin()): ?>
                    <hr class="my-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6>Admin Actions</h6>
                            <p class="text-muted mb-0">Access full administrative controls.</p>
                        </div>
                        <a href="admin.php" class="btn btn-warning">
                            <i class="bi bi-shield-check"></i> Admin Panel
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Delete Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>This action cannot be undone!</strong>
                </div>
                <p>Deleting your account will:</p>
                <ul>
                    <li>Permanently delete your profile</li>
                    <li>Remove all your stations and measurements</li>
                    <li>Delete all your collections</li>
                    <li>Remove you from all friend lists</li>
                    <li>Unshare all collections you've shared</li>
                </ul>
                <p>To confirm deletion, type your username below:</p>
                <input type="text" class="form-control" id="confirmUsername" placeholder="<?php echo htmlspecialchars($user['pk_username']); ?>">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete" disabled>
                    Delete My Account
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>