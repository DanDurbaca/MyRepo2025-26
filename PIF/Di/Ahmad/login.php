<?php include 'includes/header.php'; ?>
<div class="card">
    <h2>Login</h2>
    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $u = $conn->real_escape_string($_POST['username']);
        $p = $_POST['password'];
        
        $res = $conn->query("SELECT * FROM user WHERE pk_username='$u'");
        
        if ($user = $res->fetch_assoc()) {
            if (password_verify($p, $user['password'])) {
                // --- CRITICAL UPDATES START ---
                $_SESSION['username'] = $user['pk_username'];
                $_SESSION['role']     = $user['role']; // Save the role (admin or user)
                // --- CRITICAL UPDATES END ---
                
                header("Location: /index.php");
                exit();
            } else {
                echo "<p style='color:#f87171;'>Invalid Password</p>";
            }
        } else {
            echo "<p style='color:#f87171;'>User Not Found</p>";
        }
    }
    ?>
    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" placeholder="Username" required>
        
        <label>Password</label>
        <input type="password" name="password" placeholder="Password" required>
        
        <button type="submit">Login</button>
    </form>
    <p style="margin-top:15px; font-size:0.9em; color:#94a3b8;">
        Don't have an account? <a href="/register.php" style="color:#38bdf8;">Register here</a>
    </p>
</div>
<?php include 'includes/footer.php'; ?>