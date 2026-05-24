<!DOCTYPE html>
<html>

<head>
    <title>My Account</title>
    <link rel="stylesheet" href="../Styles/styles.css">
</head>

<body class="light-theme">
    <?php
    include("../Libraries/conndb.php");
    include("../Libraries/navbar.php");
    createnavbar("Account");
    $selectaccountdata = $conn->prepare("SELECT * FROM users WHERE pk_username = ?");
    $selectaccountdata->bind_param("s", $_SESSION["username"]);
    $selectaccountdata->execute();
    $result = $selectaccountdata->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    }
    ?>
    <h1>Edit Your Account</h1>

    <form class="form-card" method="POST">
        <div class="form-group">
            <h3>Personal Information</h3>
            <label>Username</label><input type="text" name="username" value="<?php if (isset($row["pk_username"])) echo $row['pk_username']; ?>" readonly>
            <label>Email</label><input type="email" name="email" value="<?php if (isset($row["email"])) echo $row['email']; ?>">
            <label>First Name</label><input type="text" name="firstname" value="<?php if (isset($row["firstName"])) echo $row['firstName']; ?>">
            <label>Last Name</label><input type="text" name="lastname" value="<?php if (isset($row["lastName"])) echo $row['lastName']; ?>">
            <button>Save</button>
        </div>
    </form><br>
    <form class="form-card" method="POST">
        <div class="form-group">
            <h3>Change Password</h3>
            <label>Old password</label><input type="password" name="old_password">
            <label>New password</label><input type="password" name="new_password">
            <button>Change Password</button>
        </div>
    </form>
    <?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["email"], $_POST["firstname"], $_POST["lastname"])) {
            $stmt = $conn->prepare("UPDATE users SET email = ?, firstName = ?, lastName = ? WHERE pk_username = ?");
            $stmt->bind_param("ssss", $_POST["email"], $_POST["firstname"], $_POST["lastname"], $_SESSION["username"]);
            $stmt->execute();
            echo "<p>Account updated successfully!</p>";
        }
        if (isset($_POST["old_password"], $_POST["new_password"])) {
            $checkstmt = $conn->prepare("SELECT password FROM users WHERE pk_username = ?");
            $checkstmt->bind_param("s", $_SESSION["username"]);
            $checkstmt->execute();
            $result = $checkstmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if (password_verify($_POST["old_password"], $row["password"])) {
                    $new_hashed_password = password_hash($_POST["new_password"], PASSWORD_DEFAULT);
                    $updatestmt = $conn->prepare("UPDATE users SET password = ? WHERE pk_username = ?");
                    $updatestmt->bind_param("ss", $new_hashed_password, $_SESSION["username"]);
                    $updatestmt->execute();
                    echo "<p>Password changed successfully!</p>";
                } else {
                    echo "<p>Old password is incorrect.</p>";
                }
            }
        }
    }

    ?>
</body>

</html>