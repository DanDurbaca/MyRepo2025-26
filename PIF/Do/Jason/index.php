<!-- HTML Document Setup: Defines the basic structure and metadata for the login page -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="MyCss.css?<?= time(); ?>">
    <title>Login</title>
</head>

<!-- Page Content: Displays the login form and related UI elements -->

<body>
    <?php
    include_once("commonphp.php");
    ?>
    <h1 class="Title"> Login</h1>
    <p class="Title">Please log in to enter the website.</p>
    <div class="Title">
        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button id="loginBtn" type="submit" name="LoginBtn">Login</button>
        </form>
    </div>
    <p class="Title"> If you do not have an account please register here.</p>
    <a href="Register.php" class="Title"> Register </a>

    <!-- PHP Login Processing: Handles form submission, validates input, checks database, and manages user authentication -->
    <?php
    if (isset($_POST['LoginBtn'])) {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            echo '<div class="addedProduct">Please enter username and password.</div>';
        } else {
            if ($stmt = $conn->prepare('SELECT user_ID, full_name, Upswd, UName FROM `User` WHERE LOWER(UName) = LOWER(?)')) {
                $stmt->bind_param('s', $username);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows === 1) {
                    $stmt->bind_result($userId, $fullName, $hash, $dbUsername);
                    $stmt->fetch();
                    if (substr($hash, 0, 1) === '$' ? password_verify($password, $hash) : $password == $hash) {
                        // Successful login
                        $_SESSION['user_id'] = $userId;
                        $_SESSION['username'] = $dbUsername;
                        $_SESSION['User'] = $dbUsername;
                        $_SESSION['full_name'] = $fullName;

                        // Fetch admin flag and store in session
                        $adminStmt = $conn->prepare('SELECT administrator FROM `User` WHERE user_ID = ? LIMIT 1');
                        $adminStmt->bind_param('i', $userId);
                        $adminStmt->execute();
                        $adminStmt->bind_result($adminFlag);
                        $adminStmt->fetch();
                        $adminStmt->close();
                        $_SESSION['is_admin'] = (int)$adminFlag === 1;

                        header('Location: Homepage.php');
                        exit;
                    } else {
                        echo '<div class="addedProduct">Invalid credentials.</div>';
                    }
                } else {
                    echo '<div class="addedProduct">Invalid credentials.</div>';
                }
                $stmt->close();
            } else {
                echo '<div class="addedProduct">Database error.</div>';
            }
        }
    }
    ?>
</body>

</html>