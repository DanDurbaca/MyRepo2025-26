<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
    <link rel="stylesheet" href="../Styles/styles.css">
</head>

<body class="light-theme">
    <h1>Register</h1>

    <form class="form-card" method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required><br>
            <label>Password</label>
            <input type="password" name="password" required><br>
            <label>Confirm Password</label>
            <input type="password" name="confirmpassword" required><br>
            <label>Email</label>
            <input type="email" name="email" required><br>
            <label>First Name</label>
            <input type="text" name="firstname" required><br>
            <label>Last Name</label>
            <input type="text" name="lastname" required><br>
        </div>
        <button>Create Account</button>
    </form>
    <?php
    include("../Libraries/loginlib.php");
    include("../Libraries/conndb.php");
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        form($_POST["username"], $_POST["firstname"], $_POST["lastname"], $_POST["email"], $_POST["password"], $_POST["confirmpassword"]);
    }
    function form($username, $firstname, $lastname, $email, $password, $confirmpassword)
    {
        global $conn;
        if (!isset($username, $firstname, $lastname, $email, $password, $confirmpassword)) {
            die("Please fill all in all of the fields!");
        }
        if ($password != $confirmpassword) {
            die("<p>Passwords do not match!</p>");
        }
        if (userAlreadyExists($username)) {
            die("<p>Username already exists!</p>");
        }
        if (emailAlreadyExists($email)) {
            die("<p>Email already registered!</p>");
        }
        $hashedpassword = password_hash($password, PASSWORD_DEFAULT);
        $registerstmt = $conn->prepare("INSERT INTO users (pk_username, password, email, firstName, lastName) VALUES (?, ?, ?, ?, ?)");
        $registerstmt->bind_param("sssss", $username, $hashedpassword, $email, $firstname, $lastname);
        $registerstmt->execute();
        echo "<p>You have been registered sucessfully!</p>";
        header("Location: index.php");
    }

    ?>
</body>

</html>