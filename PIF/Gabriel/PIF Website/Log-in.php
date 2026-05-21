<?php
    session_start();
    
    // Redirect if already logged in
    if (!empty($_SESSION["userNameSession"])) {
        header("Location: HomePage.php");
        exit;
    }
    
    // Database setup
    $host = "localhost";
    $db = "portableindoorfeedback";
    $user = "root";
    $pass = "";

    $conn = mysqli_connect($host, $user, $pass, $db);

    if (!$conn) {
        die("<p style='color:red'>Connection failed: " . mysqli_connect_error() . "</p>");
    }

    // Form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = trim($_POST["username"]);
        $password = trim($_POST["password"]);
        
        if (empty($username) || empty($password)) {
            $error = "<p style='color:red'>Error! Please enter both username and password!</p>";
        } else {
            // Check if user exists - use prepared statement
            $sql = "SELECT pk_username, `password` FROM user WHERE pk_username = ?";
            $stmt = mysqli_prepare($conn, $sql);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $username);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    
                    // Verify the hashed password
                    if (password_verify($password, $row['password'])) {
                        // User found, login successful
                        $_SESSION["userNameSession"] = $username;
                        header("Location: HomePage.php");
                        exit;
                    } else {
                        // Wrong password
                        $error = "<p style='color:red'>Error! Invalid username or password!</p>";
                    }
                } else {
                    // No user found with that username
                    $error = "<p style='color:red'>Error! Invalid username or password!</p>";
                }
                
                mysqli_stmt_close($stmt);
            } else {
                // SQL query error
                $error = "<p style='color:red'>Error! Database error: " . mysqli_error($conn) . "</p>";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PIF - Login</title>
    <link rel="stylesheet" href="Log-in customization.css">
</head>
<body>
    <form method="POST" action="">
        <div class="centered">
            <h1>Login</h1>
            <h2>Please enter your credentials to sign in!</h2>
            
            <?php if (isset($error)) { echo "<div class='error'>$error</div>"; } ?>
            
            <div>
                <h3>Username:</h3>
                <input type="text" name="username" required>
                
                <h3>Password:</h3>
                <div class="password-container">
                    <input type="password" name="password" class="password-input" required>
                    <button type="button" class="toggle-password" onclick="togglePassword()">Show</button>
                </div>
                
                <div>
                    <input type="submit" value="Sign In">
                </div>
                
                <div>
                    <p>Don't have an account? <a href="Registeration.php">Register now</a></p>
                </div>
            </div>
        </div>
    </form>

    <script>
        function togglePassword() {
            const passwordInput = document.querySelector('input[name="password"]');
            const toggleButton = document.querySelector('.toggle-password');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.textContent = 'Hide';
            } else {
                passwordInput.type = 'password';
                toggleButton.textContent = 'Show';
            }
        }
    </script>
</body>
</html>