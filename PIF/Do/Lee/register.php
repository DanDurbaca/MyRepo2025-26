<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./style.css?<?= time(); ?>">
    <title>Document</title>
</head>

<body>
    <nav>
        <?php
        include_once("comCode.php");
        NavigationBar("register");
        ?>
    </nav>

    <header>

    </header>

    <main class="indexMain">

        <?php
        //Logout
        if (isset($_POST["Logout"])) {
            session_unset();
            session_destroy();
    // Delete session cookie to ensure complete logout
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    // Redirect to home after logout
    header("Location: ./index.php");
    exit();
        }

        //Create new user registration
        if (isset($_POST["uName"], $_POST["uPassword"], $_POST["cPassword"], $_POST["uEmail"])) {
            if ($_POST["uPassword"] == $_POST["cPassword"]) {
                $passHash = password_hash($_POST["uPassword"], PASSWORD_DEFAULT);
                $sqlInsert = $conn->prepare("insert into User(userName,password,emailAddress,userRole) values(?,?,?,0)");
                $sqlInsert->bind_param("sss", $_POST["uName"], $passHash, $_POST["uEmail"]);
                $sqlInsert->execute();
                echo "User created successfully!";
            } else {
                echo "Passwords do not match.";
            }
        }
        
        

        //User login
        function userAlreadyExists($a, $b)
        {
            global $conn;
            $sqlPass = $conn->prepare("Select password,userId from User where userName = ?");
            $sqlPass->bind_param("s", $a);
            $sqlPass->execute();
            $passRes = $sqlPass->get_result();
            if ($passRes->num_rows > 0) {
                $row = $passRes->fetch_assoc();
                $hashedPassword = $row["password"];
                if (password_verify($b, $hashedPassword)) {
                    $_SESSION["UserLoggedIn"] = true;
                    $_SESSION["userName"] = $_POST["lName"];
                    header("Refresh:0");
                    $_SESSION["userID"] = $row["userId"];
                } else {
                    echo "Incorrect Password";
                }
            } else {
                echo "User not found";
            }
        }

        function adminAlreadyExists($a, $b)
        {
            global $conn;
            $sqlSelect = $conn->prepare("Select password, userId from User where userName = ? and userRole = 1");
            $sqlSelect->bind_param("s", $a);
            $sqlSelect->execute();
            $result = $sqlSelect->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $hashedPassword = $row["password"];
                if (password_verify($b, $hashedPassword)) {
                    $_SESSION["adminLoggedIn"] = true;
                    $_SESSION["userName"] = $_POST["lName"];
                    $_SESSION["userID"] = $row["userId"];
                    header("Refresh:0");
                }
            }
        }

        //Login called
        if (isset($_POST["lName"], $_POST["lPassword"])) {
            userAlreadyExists($_POST["lName"], $_POST["lPassword"]);
            adminAlreadyExists($_POST["lName"], $_POST["lPassword"]);
        }

        //Update Password
        if (isset($_POST['updatePassword'])) {
            $newPassword = $_POST['newPassword'];
            if (!empty($newPassword)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $sqlUpdate = $conn->prepare("Update User set password = ? where userName=?");
                $sqlUpdate->bind_param("ss", $hashedPassword, $_SESSION["userName"]);
                if ($sqlUpdate->execute()) {
                    echo "Password updated successfully!";
                }
            } else {
                echo "Please enter a password.";
            }
        }

        //Update User
        if (isset($_POST['updateUser'])) {
            $newName = $_POST['newUserName'];
            if (!empty($newName)) {
                $sqlUpdate = $conn->prepare("Update User set userName = ? where userName=?");
                $sqlUpdate->bind_param("ss", $newName, $_SESSION["userName"]);
                if ($sqlUpdate->execute()) {
                    echo "Name updated successfully!";
                    $_SESSION["userName"] = $newName;
                    header("Refresh:0");
                }
            } else {
                echo "Please enter a name.";
            }
        }

        //Update name
        if (isset($_POST['updateName'])) {
            $newFirstName = $_POST['newName'];
            $newLastName = $_POST['newLastName'];
            if (!empty($newFirstName)) {
                $sqlUpdate = $conn->prepare("Update User set firstName = ? where userName = ?");
                $sqlUpdate->bind_param("ss", $newFirstName, $_SESSION["userName"]);
                if ($sqlUpdate->execute()) {
                    echo "First name updated successfully!";
                }
            }
            if (!empty($newLastName)) {
                $sqlUpdate = $conn->prepare("Update User set lastName = ? where userName = ?");
                $sqlUpdate->bind_param("ss", $newLastName, $_SESSION["userName"]);
                if ($sqlUpdate->execute()) {
                    echo "Last name updated successfully!";
                }
            }
            if (empty($newFirstName) && empty($newLastName)) {
                echo "Please enter a first name or last name.";
            }
        }

        //Friend System
        
        //Accept Friend
        $sqlSelect = $conn->prepare("select userName, userId from User");
        $sqlSelect->execute();
        $result = $sqlSelect->get_result();
        while ($row = $result->fetch_assoc()) {
            if (isset($_POST[$row["userName"]])) {
                $sqlUpdate = $conn->prepare("update Friendlist set requestStatus = 1 where friendListId = ? and user = ?");
                $sqlUpdate->bind_param("ii", $_SESSION["userID"], $row["userId"]);
                $sqlUpdate->execute();
                echo "Friend request accepted";

                $friendMutual = $conn->prepare("insert into Friendlist (friendlistId,user,requestStatus) values(?,?,1)");
                $friendMutual->bind_param("ii", $row["userId"], $_SESSION["userID"]);
                $friendMutual->execute();
            }
        }

        //End Friendship
        $sqlSelect = $conn->prepare("select userName, userId from User");
        $sqlSelect->execute();
        $result = $sqlSelect->get_result();
        while ($row = $result->fetch_assoc()) {
            if (isset($_POST[$row["userName"] . "End"])) {
                // Delete both directions of the friendship
                $removeFriend = $conn->prepare("delete from Friendlist where (friendListId = ? and user = ?) or (friendListId = ? and user = ?)");
                $removeFriend->bind_param("iiii", $_SESSION["userID"], $row["userId"], $row["userId"], $_SESSION["userID"]);
                $removeFriend->execute();
                echo "Friendship ended";
                header("Refresh:0");
            }
        }


        //Send friend request
        if (isset($_POST["addFriend"])) {
            $friendName = $_POST["newFriend"];
            if ($friendName == $_SESSION["userName"]) {
                echo "You cannot add yourself as a friend.";
            } else {
                $sqlSelect = $conn->prepare("SELECT userId FROM User WHERE userName = ?");
                $sqlSelect->bind_param("s", $friendName);
                $sqlSelect->execute();
                $result = $sqlSelect->get_result();
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    // Check if friend request already exists
                    $checkRequest = $conn->prepare("select friendListId from Friendlist where friendListId = ? and user = ?");
                    $checkRequest->bind_param("ii", $row["userId"], $_SESSION["userID"]);
                    $checkRequest->execute();
                    $checkReqResult = $checkRequest->get_result();
                    
                    if ($checkReqResult->num_rows == 0) {
                        $sqlInsert = $conn->prepare("INSERT INTO Friendlist (friendListId, user, requestStatus) VALUES (?, ?, 0)");
                        $sqlInsert->bind_param("ii", $row["userId"], $_SESSION["userID"]);
                        $sqlInsert->execute();
                        echo "Friend request sent.";
                        header("Refresh:0");
                    } else {
                        echo "Friend request already sent or friend exists.";
                    }
                } else {
                    echo "User not found.";
                }
            }
        }


        if (!empty($_SESSION["UserLoggedIn"]) || !empty($_SESSION["adminLoggedIn"])) {
            ?>
            <h1><?= htmlspecialchars($text['changePassword']) ?></h1>
            <form method="POST">
                <div class="form-row">
                    <label for="newPassword"><?= htmlspecialchars($text['changePassword']) ?></label>
                    <input type="password" name="newPassword" id="newPassword" placeholder="Enter your new password">
                    <div class="form-actions">
                        <input type="submit" name="updatePassword" value="<?= htmlspecialchars($text['change']) ?>">
                    </div>
                </div>
            </form>

            <h1><?= htmlspecialchars($text['changeUsername']) ?></h1>
            <form method="POST">
                <div class="form-row">
                    <label for="newUserName"><?= htmlspecialchars($text['changeUsername']) ?></label>
                    <input type="text" name="newUserName" id="newUserName" placeholder="Enter your new user name">
                    <div class="form-actions">
                        <input type="submit" name="updateUser" value="<?= htmlspecialchars($text['change']) ?>">
                    </div>
                </div>
            </form>

            <h1><?= htmlspecialchars($text['changeName']) ?></h1>
            <form method="POST">
                <div class="form-row">
                    <label for="newName"><?= htmlspecialchars($text['changeName']) ?></label>
                    <input type="text" name="newName" id="newName" placeholder="Enter your new first name">
                    <input type="text" name="newLastName" id="newLastName" placeholder="Enter your new last name">
                    <div class="form-actions">
                        <input type="submit" name="updateName" value="<?= htmlspecialchars($text['change']) ?>">
                    </div>
                </div>
            </form>

            <h1><?= htmlspecialchars($text['addFriend']) ?></h1>
            <form method="POST">
                <div class="form-row">
                    <label for="newFriend"><?= htmlspecialchars($text['addFriend']) ?></label>
                    <input type="text" name="newFriend" id="newFriend" placeholder="Enter user name">
                    <div class="form-actions">
                        <input type="submit" name="addFriend" value="<?= htmlspecialchars($text['change']) ?>">
                    </div>
                </div>
            </form>

            <h1>Friend Requests </h1>
            <?php
            //Show Friend Request
            $sqlSelect = $conn->prepare("select * from Friendlist where friendListId = ? and requestStatus = 0");
            $sqlSelect->bind_param("i", $_SESSION["userID"]);
            $sqlSelect->execute();
            $result = $sqlSelect->get_result();
            while ($row = $result->fetch_assoc()) {
                ?>
                <table>
                    <th>
                        <?php
                        $sqlSelect = $conn->prepare("select userName from User where userId = ?");
                        $sqlSelect->bind_param("i", $row["user"]);
                        $sqlSelect->execute();
                        $result = $sqlSelect->get_result();
                        while ($row = $result->fetch_assoc()) {
                            print ("User " . $row["userName"] . " wants to be friends");
                            ?>
                            <form method="POST">
                                <input type="submit" name="<?= $row["userName"] ?>"
                                    value="<?= htmlspecialchars($text['accept']) ?>">
                            </form>
                            <?php
                        }
                        ?>
                    </th>
                </table>

                <?php
            }
            ?>
            <h1>Your current friends</h1>
            <?php
            //Show friends
            $sqlSelect = $conn->prepare("select * from Friendlist where (friendListId = ? or user = ?) and requestStatus = 1");
            $sqlSelect ->bind_param("ii", $_SESSION["userID"], $_SESSION["userID"]);
            $sqlSelect->execute();
            $result = $sqlSelect->get_result();

            while ($row = $result->fetch_assoc()) {
                ?>
                <table>
                    <th>
                        <?php
                        $sqlSelect = $conn->prepare("select userName from User where userId = ?");
                        $sqlSelect->bind_param("i", $row["user"]);
                        $sqlSelect->execute();
                        $result = $sqlSelect->get_result();
                        while ($row = $result->fetch_assoc()) {
                            print ($row["userName"]);
                            ?>
                            <form method="POST">
                                <input type="submit" name="<?= $row["userName"] . "End" ?>"
                                    value="<?= htmlspecialchars($text['endFriendship']) ?>">
                            </form>
                            <?php
                        }
                        ?>
                    </th>
                </table>
                <?php
            }
        } else {
            ?>
            <h1 style="color: white;"> <?= $text["regLogIn"] ?></h1>
            <form method="POST">
                <div class="form-row">
                    <label for="lName">Username</label>
                    <input type="text" placeholder="Name" name="lName" id="lName">
                    <input type="password" placeholder="<?= $text["regPas"] ?>" name="lPassword" id="lPassword">
                    <div class="form-actions">
                        <input type="submit" value="<?= $text["regCon"] ?>">
                    </div>
                </div>
            </form>

            <h1 style="color: white;"> <?= $text["regReg"] ?></h1>
            <form method="POST">
                <div class="form-row">
                    <label for="uName">Create account</label>
                    <input type="text" placeholder="Name" name="uName" id="uName">
                    <input type="password" placeholder="<?= $text["regPas"] ?>" name="uPassword" id="uPassword">
                    <input type="password" placeholder="<?= $text["regPasCon"] ?>" name="cPassword" id="cPassword">
                    <input type="text" placeholder="<?= $text["regPasEmail"] ?>" name="uEmail" id="uEmail">
                    <div class="form-actions">
                        <input type="submit" value="<?= $text["regCre"] ?>">
                    </div>
                </div>
            </form>
            <?php
        }
        ?>

        <!-- Logout button moved here for prominence -->
        <?php if (!empty($_SESSION["UserLoggedIn"]) || !empty($_SESSION["adminLoggedIn"])) { ?>
            <form method="POST" aria-label="Logout" style="margin-top:18px;">
                <div style="display:flex;justify-content:flex-end">
                    <button type="submit" name="Logout" class="icon-btn icon-btn-lg"
                        title="<?= htmlspecialchars($text["regLogOut"]) ?>">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M16 17L21 12L16 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M13 5H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h7" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span class="btn-label"><?= htmlspecialchars($text["regLogOut"]) ?></span>
                    </button>
                </div>
            </form>
        <?php } ?>

    </main>
    <footer>
        <article></article>
    </footer>

</body>

</html>