<?php
// pages/friends_view.php
// View for managing friends and friend requests

// Extract data passed from controller
$friends = $view_data['friends'] ?? [];                // Current friends list
$pending_requests = $view_data['pending_requests'] ?? []; // Incoming friend requests
$success_message = $view_data['success_message'] ?? '';  // Success message
$error_message   = $view_data['error_message'] ?? '';    // Error message

// Helper: generate initials for avatar display
function initials($first, $last) {
    return strtoupper(substr($first,0,1) . substr($last,0,1)); // First letters of first & last name
}

// Helper: render a friend card
function renderFriendCard($user, $type = 'current') {
    $avatar = initials($user['firstName'], $user['lastName']); // Avatar initials
    $username = htmlspecialchars($user['pk_username']);       // Username
    $fullName = htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); // Full name
    $email = $user['email'] ?? '';                             // Email
    $pending = ($type === 'pending');                           // Pending request flag
?>

    <div class="friend-card <?= $pending ? 'pending' : '' ?>">
        <div class="friend-avatar"><?= $avatar ?></div> <!-- Avatar circle -->
        <div class="friend-info">
            <strong><?= $fullName ?></strong>
            <p>@<?= $username ?></p>
            <?php if ($pending): ?>
                <p class="muted">Waiting for you to respond</p> <!-- Pending message -->
            <?php else: ?>
                <p><?= htmlspecialchars($email) ?></p> <!-- Show email for current friend -->
            <?php endif; ?>
        </div>

        <!-- Friend action form -->
        <form method="post" class="friend-actions"
              <?= $pending ? '' : 'onsubmit="return confirm(\'Remove this friend?\');"' ?>>
            <input type="hidden" name="friend_username" value="<?= $username ?>">

            <?php if ($pending): ?>
                <button type="submit" name="action" value="add_friend" class="btn btn-primary btn-sm">Accept</button>
                <button type="submit" name="action" value="reject_friend" class="btn btn-danger btn-sm">Reject</button>
            <?php else: ?>
                <input type="hidden" name="action" value="remove_friend"> <!-- Remove current friend -->
                <button type="submit" class="btn btn-danger btn-sm">Remove</button>
            <?php endif; ?>
        </form>
    </div>

<?php
} // End renderFriendCard
?>

<?php include __DIR__ . '/../includes/header.php'; ?> <!-- Header/navigation -->

<main class="container">

    <!-- Page title -->
    <h1>My Friends</h1>
    <p>Manage your friends and incoming friend requests.</p>

    <!-- Display messages -->
    <?php if ($success_message || $error_message): ?>
        <div class="alert <?= $success_message ? 'alert-success' : 'alert-danger' ?>">
            <?= htmlspecialchars($success_message ?: $error_message) ?>
        </div>
    <?php endif; ?>

    <!-- Section: Add a new friend -->
    <section>
        <h2>Add New Friend</h2>
        <form method="post" class="form-row">
            <input type="hidden" name="action" value="add_friend"> <!-- Identify action -->
            <input type="text" name="friend_username" placeholder="Friend's username" required>
            <button type="submit" class="btn btn-primary">Add Friend</button>
        </form>
    </section>

    <hr>

    <!-- Section: Pending friend requests -->
    <?php if ($pending_requests): ?>
        <section>
            <h2>Friend Requests</h2>
            <p class="muted">These users added you as a friend.</p>
            <div class="grid grid-2">
                <?php foreach ($pending_requests as $p) renderFriendCard($p, 'pending'); ?>
            </div>
        </section>
        <hr>
    <?php endif; ?>

    <!-- Section: Current friends -->
    <section>
        <h2>Current Friends</h2>
        <?php if ($friends): ?>
            <div class="grid grid-2">
                <?php foreach ($friends as $f) renderFriendCard($f); ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>No friends yet</h3>
                <p>Add friends using their username above.</p>
            </div>
        <?php endif; ?>
    </section>

</main>

<?php include __DIR__ . '/../includes/footer.php'; ?> <!-- Footer -->