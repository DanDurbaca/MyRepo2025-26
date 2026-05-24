<?php
    // Start session and redirect if already logged in
    session_start();
    if (!empty($_SESSION["userNameSession"])) {
        header("Location: HomePage.php");
        exit;
    }
    
    //4: started with the database setup for the users
    $host = "localhost";
    $db = "portableindoorfeedback";
    $user = "root";
    $pass = "";

    $conn = mysqli_connect($host, $user, $pass, $db);

    if (!$conn) {
        die("<p style='color:red'>Connection failed: " . mysqli_connect_error() . "</p>");
    }

    //3: then i started with the session
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = trim($_POST["username"]);
        $userFirstName = trim($_POST["firstName"]);
        $userLastName = trim($_POST["lastName"]);
        $userEmail = trim($_POST["userEmail"]);
        $userPasswd = trim($_POST["password"]);
        
        if (empty($username) || empty($userFirstName) || empty($userLastName) || empty($userEmail) || empty($userPasswd)) {
            $error = "<p style='color:red'>Error! Information Missing!</p>";
        } else {
            // Check if username or email already exists
            $check_sql = "SELECT pk_username, email FROM user WHERE pk_username = '$username' OR email = '$userEmail'";
            $check_result = mysqli_query($conn, $check_sql);
            
            if (mysqli_num_rows($check_result) > 0) {
                $error = "<p style='color:red'>Error! Username or email already exists!</p>";
            } else {
                // Hash the password for security
                $hashedPassword = password_hash($userPasswd, PASSWORD_DEFAULT);
                
                // Use prepared statement to prevent SQL injection
                $sql = "INSERT INTO user (pk_username, firstName, lastName, `password`, email, `role`) VALUES (?, ?, ?, ?, ?, 'User')";
                $stmt = mysqli_prepare($conn, $sql);
                
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "sssss", $username, $userFirstName, $userLastName, $hashedPassword, $userEmail);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $_SESSION["userNameSession"] = $username;
                        header("Location: HomePage.php");
                        exit;
                    } else {
                        $error = "<p style='color:red'>Error! Could not create account: " . mysqli_error($conn) . "</p>";
                    }
                    
                    mysqli_stmt_close($stmt);
                } else {
                    $error = "<p style='color:red'>Error! Database error: " . mysqli_error($conn) . "</p>";
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PIF - Registration</title>
    <link rel="stylesheet" href="Registration customization.css">

</head>
<body>
    <form method="POST" action="">
        <div class="centered">
            <h1>Registration</h1>
            <h2>Please fill out the following form to register an account!</h2>
            
            <?php if (isset($error)) { echo "<div class='error'>$error</div>"; } ?>
            
            <div>
                <h3>Username:</h3>
                <input type="text" name="username" required>
                
                <h3>First Name:</h3>
                <input type="text" name="firstName" required>
               
                <h3>Last Name:</h3>
                <input type="text" name="lastName" required>
                
                <h3>Email:</h3>
                <input type="email" name="userEmail" required>
                
                <h3>Password:</h3>
                <div class="password-container">
                    <input type="password" name="password" class="password-input" required>
                    <button type="button" class="toggle-password" onclick="togglePassword()">Show</button>
                </div>
                
                <div>
                    <input type="submit" value="Sign Up">
                </div>
                
                <div>
                    <p>Already have an account? <a href="Log-in.php">Log in</a></p>
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