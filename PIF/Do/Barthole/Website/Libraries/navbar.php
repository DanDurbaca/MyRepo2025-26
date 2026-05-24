<?php
include("conndb.php");
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function createnavbar($isselected)
{
    global $conn;
    $websites = [
        "Dashboard"   => "dashboard.php",
        "Stations"    => "stations.php",
        "Collections" => "collections.php",
        "Measurements" => "measurements.php",
        "Friends"     => "friends.php",
        "Account"     => "account.php",
    ];

    if (isset($_SESSION["username"])) {
        $websites["Logout"] = "logout.php";
    } else {
        $websites["Login"] = "login.php";
    }
    $getrolestmt = $conn->prepare("SELECT role FROM users WHERE pk_username = ?");
    $getrolestmt->bind_param("s", $_SESSION['username']);
    $getrolestmt->execute();
    $row = $getrolestmt->get_result()->fetch_assoc();
    if (isset($row["role"])) {
        if ($row['role'] === 'admin' || $row['role'] === 'Admin') {
            $_SESSION["role"] = "Admin";
            $websites["Admin"] = "admin.php";
        }
    }
    echo "<div class='navbar'>";
    foreach ($websites as $name => $url) {
        $class = ($isselected === $name) ? 'nav-child selected' : 'nav-child'; ?>

        <a href="<?= htmlspecialchars($url) ?>" class="<?= $class ?>">
            <?= htmlspecialchars($name)  ?> </a>

    <?php
    }
    ?>
    <select>
        <option value="light" <?php if (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'light') echo 'selected'; ?>>Light Theme</option>
        <option value="dark" <?php if (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') echo 'selected'; ?>>Dark Theme</option>
    </select>
    </div>

<?php
}
?>