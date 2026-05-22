<?php
// navbar.php
?>
<nav class="navbar">
    <div class="nav-brand">Portable Indoor Feedback</div> <!-- Logo name -->    
    <div class="nav-links">
        <a href="home_page.php">Home</a> <!-- Home link -->
        <?php if(isset($_SESSION["username"])): ?>
            <a href="stations.php">Stations</a> <!-- Stations link -->
            <a href="measurements.php">Measurements</a> <!-- Measurements link -->
            <a href="collection.php">Collections</a>   <!-- Collections link -->
            <a href="usr_page.php">Profile</a> <!-- User Profile link -->
            <?php if($_SESSION["role"] === "Admin"): ?>
                <a href="adminpage.php">Admin</a> <!-- Admin link for admin users -->
            <?php endif; ?>
            <a href="login.php?logout=1" style="color: #dc2626;">Logout</a> <!-- Logout link -->
            <span style="margin-left: 15px; color: #64748b;">
                Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>
            </span>
        <?php else: ?>
            <a href="login.php">Login</a> <!-- Login link -->
            <a href="create_account.php">Register</a> <!-- Link to create account -->
        <?php endif; ?>
    </div>
</nav>