<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./style.css?">
    <title>Home</title>
</head>

<body>

    <nav>
        <?php
        include_once("comCode.php");
        NavigationBar("home");
        ?>
    </nav>

    <header>
    </header>

    <main class="indexMain">
        <?php
        $sqlSelect = $conn->prepare("select * from user where userId = ?");
        $sqlSelect->bind_param("i", $_GET["id"]);
        $sqlSelect->execute();
        $result = $sqlSelect->get_result();
        $row = $result->fetch_assoc();
        ?>
        <h1>User: <?= $row["userName"] ?></h1>
        <form method="POST">
            <div class="form-row">
                <label for=""><?= htmlspecialchars($text['changeUsername']) ?>:</label>
                <input type="text" name="chUserName" id="" placeholder="<?= $row["userName"] ?>">
                <input type="submit" name="chUserNameCon" id="" value="<?= htmlspecialchars($text['change']) ?>">
            </div>

            <div class="form-row">
                <label for=""><?= htmlspecialchars($text['changeName']) ?>:</label>
                <input type="text" name="chFirstName" placeholder="<?= $row["firstName"] ?>">
                <input type="text" name="chLastName" placeholder="<?= $row["lastName"] ?>">
                <input type="submit" name="chNameCon" id="" value="<?= htmlspecialchars($text['change']) ?>">
            </div>

            <div class="form-row">
                <label for=""><?= htmlspecialchars($text['changeEmail']) ?>:</label>
                <input type="text" name="chEmailValue" placeholder="<?= $row["emailAddress"] ?>">
                <input type="submit" name="chEmail" id="" value="<?= htmlspecialchars($text['change']) ?>">
            </div>

            <div class="form-row">
                <label for=""><?= htmlspecialchars($text['changeRole']) ?></label>
                <input type="number" name="chRole" placeholder="<?= $row["userRole"] ?>">
                <input type="submit" name="chRoleCon" id="" value="<?= htmlspecialchars($text['change']) ?>">
            </div>
        </form>
        <?php
        //Change username
        if (isset($_POST['chUserNameCon'])) {
            $name = $_POST["chUserName"];
            if (!empty($name)) {
                $sqlUpdate = $conn->prepare("Update User set userName = ? where userId = ?");
                $sqlUpdate->bind_param("si", $name, $row["userId"]);
                if ($sqlUpdate->execute()) {
                    echo "Name updated successfully!";
                    header("Refresh:0");
                }
            } else {
                echo "Please enter a name.";
            }
        }

        //Change name
            if (isset($_POST['chNameCon'])) {
            $firstName = $_POST["chFirstName"];
            $lastName = $_POST["chLastName"];
            if (!empty($firstName && $lastName)) {
                $sqlUpdate = $conn->prepare("Update User set userName = ? where userId = ?");
                $sqlUpdate->bind_param("si", $name, $row["userId"]);
                if ($sqlUpdate->execute()) {
                    echo "Name updated successfully!";
                    header("Refresh:0");
                }
            } 
            else if (!empty($firstName)) {
                $sqlUpdate = $conn->prepare("Update User set firstName = ? where userId = ?");
                $sqlUpdate->bind_param("si", $firstName, $row["userId"]);
                if ($sqlUpdate->execute()) {
                    header("Refresh:0");
                }                  
            }
            else if (!empty($lastName)) {
                $sqlUpdate = $conn->prepare("Update User set lastName = ? where userId = ?");
                $sqlUpdate->bind_param("si", $lastName, $row["userId"]);
                if ($sqlUpdate->execute()) {
                    header("Refresh:0");
                }             
            
            else {
                echo "Please enter a new name.";
            }
            }
        }

        //Change Email
        if (isset($_POST['chEmailCon'])) {
            $email = $_POST["chEmail"];
            if (!empty($email)) {
                $sqlUpdate = $conn->prepare("Update User set emailAddress = ? where userId = ?");
                $sqlUpdate->bind_param("si", $email, $row["userId"]);
                if ($sqlUpdate->execute()) {
                    header("Refresh:0");
                }
            } else {
                echo "Please enter an Email";
            }
        }

        //Change Role
        if (isset($_POST['chRoleCon'])) {
            $role = $_POST["chRole"];
            if (!empty($role)) {
                $sqlUpdate = $conn->prepare("Update User set emailAddress = ? where userId = ?");
                $sqlUpdate->bind_param("si", $role, $row["userId"]);
                if ($sqlUpdate->execute()) {
                    header("Refresh:0");
                }
            } else {
                echo "Please enter value";
            }
        }

        ?>


    </main>
    <footer>
        <article></article>
    </footer>
</body>

</html>