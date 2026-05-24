<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$pageTitle = 'Friends';
$message = '';

// Handle friend operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_friend') {
        $friendUsername = sanitize($_POST['friend_username']);
        
        // Check if user exists
        $stmt = $pdo->prepare("SELECT pk_username FROM user WHERE pk_username = ?");
        $stmt->execute([$friendUsername]);
        
        if (!$stmt->fetch()) {
            $message = "User not found.";
        } else if ($friendUsername === $_SESSION['username']) {
            $message = "You cannot add yourself as a friend.";
        } else {
            // Check if already friends
            $stmt = $pdo->prepare("
                SELECT 1 FROM isfriend 
                WHERE (pkfk_user_user = ? AND pkfk_user_friend = ?)
                OR (pkfk_user_user = ? AND pkfk_user_friend = ?)
            ");
            $stmt->execute([$_SESSION['username'], $friendUsername, $friendUsername, $_SESSION['username']]);
            
            if ($stmt->fetch()) {
                $message = "You are already friends with this user.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO isfriend (pkfk_user_user, pkfk_user_friend) VALUES (?, ?)");
                if ($stmt->execute([$_SESSION['username'], $friendUsername])) {
                    $message = "Friend added successfully!";
                } else {
                    $message = "Failed to add friend.";
                }
            }
        }
    }
    
    elseif ($action === 'remove_friend') {
        $friendUsername = sanitize($_POST['friend_username']);
        
        $stmt = $pdo->prepare("
            DELETE FROM isfriend 
            WHERE (pkfk_user_user = ? AND pkfk_user_friend = ?)
            OR (pkfk_user_user = ? AND pkfk_user_friend = ?)
        ");
        if ($stmt->execute([$_SESSION['username'], $friendUsername, $friendUsername, $_SESSION['username']])) {
            // Also remove shared collections between users
            $stmt = $pdo->prepare("
                DELETE FROM hasaccess 
                WHERE pkfk_user = ? AND pkfk_collection IN (
                    SELECT pk_collection FROM collection WHERE fk_user_creates = ?
                )
            ");
            $stmt->execute([$friendUsername, $_SESSION['username']]);
            
            $stmt = $pdo->prepare("
                DELETE FROM hasaccess 
                WHERE pkfk_user = ? AND pkfk_collection IN (
                    SELECT pk_collection FROM collection WHERE fk_user_creates = ?
                )
            ");
            $stmt->execute([$_SESSION['username'], $friendUsername]);
            
            $message = "Friend removed successfully!";
        } else {
            $message = "Failed to remove friend.";
        }
    }
}

// Get user's friends
$stmt = $pdo->prepare("
    SELECT u.pk_username, u.firstName, u.lastName, u.email
    FROM user u 
    WHERE u.pk_username IN (
        SELECT pkfk_user_friend FROM isfriend WHERE pkfk_user_user = ?
        UNION
        SELECT pkfk_user_user FROM isfriend WHERE pkfk_user_friend = ?
    )
    ORDER BY u.firstName, u.lastName
");
$stmt->execute([$_SESSION['username'], $_SESSION['username']]);
$friends = $stmt->fetchAll();

// Function to get shared collections count for a friend
function getSharedCollectionsCount($pdo, $currentUser, $friendUsername) {
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT h.pkfk_collection) as count
        FROM hasaccess h
        JOIN collection c ON h.pkfk_collection = c.pk_collection
        WHERE (h.pkfk_user = ? AND c.fk_user_creates = ?)
        OR (h.pkfk_user = ? AND c.fk_user_creates = ?)
    ");
    $stmt->execute([$currentUser, $friendUsername, $friendUsername, $currentUser]);
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

// Get friend suggestions (users who are not friends yet)
$stmt = $pdo->prepare("
    SELECT pk_username, firstName, lastName, email 
    FROM user 
    WHERE pk_username != ? 
    AND pk_username NOT IN (
        SELECT pkfk_user_friend FROM isfriend WHERE pkfk_user_user = ?
        UNION
        SELECT pkfk_user_user FROM isfriend WHERE pkfk_user_friend = ?
    )
    ORDER BY firstName, lastName
    LIMIT 10
");
$stmt->execute([$_SESSION['username'], $_SESSION['username'], $_SESSION['username']]);
$suggestions = $stmt->fetchAll();

$pageJS = 'friends.js';
?>
<?php include 'includes/header.php'; ?>

<div class="main-content">
    <nav class="navbar navbar-light bg-white rounded mb-4">
        <div class="container-fluid">
            <h2 class="navbar-brand mb-0">Friends</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFriendModal">
                <i class="bi bi-person-plus"></i> Add Friend
            </button>
        </div>
    </nav>
    
    <?php if ($message): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Friends List -->
    <div class="row">
        <?php if (empty($friends)): ?>
            <div class="col-12">
                <div class="alert alert-warning">
                    <i class="bi bi-people"></i> You don't have any friends yet. Add some friends to share collections!
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($friends as $friend): 
                $sharedCount = getSharedCollectionsCount($pdo, $_SESSION['username'], $friend['pk_username']);
            ?>
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <?php echo htmlspecialchars($friend['firstName'] . ' ' . $friend['lastName']); ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($friend['pk_username']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($friend['email']); ?></p>
                        <p><strong>Shared Collections:</strong> 
                            <span class="badge bg-success"><?php echo $sharedCount; ?></span>
                        </p>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="collections.php?view_shared=<?php echo urlencode($friend['pk_username']); ?>" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-collection"></i> View Shared
                            </a>
                            <button class="btn btn-sm btn-outline-danger" 
                                    onclick="removeFriend('<?php echo $friend['pk_username']; ?>')">
                                <i class="bi bi-person-dash"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Friend Suggestions -->
    <?php if (!empty($suggestions)): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">People You May Know</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($suggestions as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></td>
                            <td><?php echo htmlspecialchars($user['pk_username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-success" 
                                        onclick="addFriend('<?php echo $user['pk_username']; ?>')">
                                    <i class="bi bi-person-plus"></i> Add Friend
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Add Friend Modal -->
<div class="modal fade" id="addFriendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add_friend">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title">Add Friend</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="friend_username" 
                               placeholder="Enter username" required>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        Enter the exact username of the person you want to add as a friend.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Friend</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Remove Friend Confirmation -->
<form method="POST" id="removeFriendForm" style="display: none;">
    <input type="hidden" name="action" value="remove_friend">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="friend_username" id="removeFriendUsername">
</form>

<?php include 'includes/footer.php'; ?>