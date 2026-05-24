<?php include 'includes/header.php'; ?>
<div class="card">
    <h2>Register</h2>
    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $u = $conn->real_escape_string($_POST['username']);
        $fn = $conn->real_escape_string($_POST['firstName']);
        $ln = $conn->real_escape_string($_POST['lastName']);
        $em = $conn->real_escape_string($_POST['email']);
        $pw = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $sql = "INSERT INTO user (pk_username, firstName, lastName, email, password) VALUES ('$u', '$fn', '$ln', '$em', '$pw')";
        if ($conn->query($sql)) header("Location: /login.php");
        else echo "Error: " . $conn->error;
    }
    ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="text" name="firstName" placeholder="First Name" required>
        <input type="text" name="lastName" placeholder="Last Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Sign Up</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
