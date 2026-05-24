<?php
// Ensure admin-only access
include_once("comCode.php");

// Redirect non-admin users
if (!isset($_SESSION["adminLoggedIn"])) {
    header("Location: index.php");
    exit;
}

// Handle form submission BEFORE any HTML output
if (isset($_POST['createUser'])) {
    $userName = $_POST["chUserName"];
    $firstName = $_POST["chFirstName"];
    $lastName = $_POST["chLastName"];
    $email = $_POST["chEmail"];
    $role = $_POST["chRole"];
    $password = $_POST["chPassword"];
    $passHash = password_hash($password, PASSWORD_DEFAULT);
    if (!empty($userName && $firstName && $lastName && $email && $role && $passHash)) {
        $sqlUpdate = $conn->prepare("insert into user (userName,firstName,lastName,userRole,emailAddress,password) values(?,?,?,?,?,?)");
        $sqlUpdate->bind_param("sssiss", $userName, $firstName, $lastName, $role, $email, $passHash);
        if ($sqlUpdate->execute()) {
            header("Refresh:0");
            exit;
        }
    } else {
        $error_message = "Please fill out every space";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./style.css?<?= time(); ?>">
</head>

<body>
    <nav>
        <?php NavigationBar("database"); ?>
    </nav>
    </nav>

    <main class="indexMain">

    <h2><?= htmlspecialchars($text['allUsers']) ?></h2>
    <table class="admin-table">
        <tr>
            <th>User ID</th>
            <th>Username</th>
            <th>Firstname</th>
            <th>Lastname</th>
            <th>Email Address</th>
            <th>Role</th>
        </tr>
        <?php
        //Show friends
        $sqlSelect = $conn->prepare("select * from user");
        $sqlSelect->execute();
        $result = $sqlSelect->get_result();
        while ($row = $result->fetch_assoc()) {
            ?>

            <tr>
                <td>
                    <?= $row["userId"] ?>
                </td>
                <td>
                    <?= $row["userName"] ?>
                </td>
                <td>
                    <?= $row["firstName"] ?>
                </td>
                <td>
                    <?= $row["lastName"] ?>
                </td>
                <td>
                    <?= $row["emailAddress"] ?>
                </td>
                <td>
                    <?= $row["userRole"] ?>
                </td>
                <td>
                    <a href="./editUser.php?id=<?= $row["userId"] ?>">Edit this User</a>
                </td>
            </tr>
            <?php
        }
        ?>
    </table>

    <h2><?= htmlspecialchars($text['createUserPage']) ?></h2>
        <form method="POST">
            <div>
                <label for="">Username: </label>
                <input type="text" name="chUserName" id="">
            </div>

            <div>
                <label for="">Password: </label>
                <input type="text" name="chPassword">
            </div>

            <div>
                <label for="">Name: </label>
                <input type="text" name="chFirstName" placeholder="First Name">
                <input type="text" name="chLastName" placeholder="Last Name">
            </div>

            <div>
                <label for="">Email Address: </label>
                <input type="text" name="chEmail">
            </div>

            <div>
                <label for="">Role</label>
                <input type="number" name="chRole">
            </div>

            <input type="submit" name="createUser" id="" value="<?= htmlspecialchars($text['createUser']) ?>">
        </form>
        <?php
            if (isset($error_message)) {
                echo $error_message;
            }
        ?>
    </main>
</body>

</html>