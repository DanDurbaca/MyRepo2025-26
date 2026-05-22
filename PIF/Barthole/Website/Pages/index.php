<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <link rel="stylesheet" href="../Styles/styles.css">
</head>

<body class="light-theme">
    <?php
    include("../Libraries/navbar.php");
    include("../Libraries/loginlib.php");
    include("../Libraries/conndb.php");
    ?>
    <form class="form-card" method="POST">
        <h1>Login</h1>
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button>Login</button>
        <p>No account? <a class="ahref" href="register.php">Register here</a></p>
    </form>
    <?php
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $user = trim($_POST['username']);
        $password = $_POST['password'];
        $stmt = $conn->prepare("SELECT * FROM users WHERE pk_username = ?");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $hashed_password_from_db = $row['password'];

            if (password_verify($password, $hashed_password_from_db)) {
                $_SESSION['id'] = $row['user_id'];
                $_SESSION['username'] = $user;
                echo "<script>window.location.href = 'dashboard.php';</script>";
                exit;
            } else {
                echo "Invalid username or password. Try again.";
            }
        } else {
            echo "Invalid username or password. Try again.";
        
        }
        
        $stmt->close();
        $conn->close();
    }
    ?>
</body>

</html>