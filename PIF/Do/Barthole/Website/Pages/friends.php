<!DOCTYPE html>
<html>

<head>
    <title>Friends</title>
    <link rel="stylesheet" href="../Styles/styles.css">
</head>

<body class="light-theme">

    <?php
    include("../Libraries/navbar.php");
    include("../Libraries/loginlib.php");
    include("../Libraries/conndb.php");
    createnavbar("Friends");
    function normalizeUsers($a, $b)
    {
        return ($a < $b) ? [$a, $b] : [$b, $a];
    }
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        /* Send friend request */
        if (isset($_POST['send_request'])) {
            [$u1, $u2] = normalizeUsers($_SESSION['username'], $_POST['send_request']);

            $stmt = $conn->prepare("
            INSERT IGNORE INTO isfriend (user_one, user_two, status, action_user)
            VALUES (?, ?, 'pending', ?)
        ");
            $stmt->bind_param("sss", $u1, $u2, $_SESSION['username']);
            $stmt->execute();
            $stmt->close();
        }

        // Accept request 
        if (isset($_POST['accept'])) {
            $stmt = $conn->prepare("UPDATE isfriend SET status = 'accepted' WHERE user_one = ? AND user_two = ?");
            $stmt->bind_param("ss", $_POST['u1'], $_POST['u2']);
            $stmt->execute();
            $stmt->close();
        }

        // Decline request 
        if (isset($_POST['decline'])) {
            $stmt = $conn->prepare("DELETE FROM isfriend WHERE user_one = ? AND user_two = ?");
            $stmt->bind_param("ss", $_POST['u1'], $_POST['u2']);
            $stmt->execute();
            $stmt->close();
        }

        // Unfriend 
        if (isset($_POST['unfriend'])) {
            $stmt = $conn->prepare("DELETE FROM isfriend WHERE user_one = ? AND user_two = ?");
            $stmt->bind_param("ss", $_POST['u1'], $_POST['u2']);
            $stmt->execute();
            $stmt->close();
        }
    }
    ?>
    <?php
    if (!isset($_SESSION['username'])) {
        die("You must be logged in.");
    }
    ?>
    <div class="friends-page">

        <div class="page-header">
            <h1>Friends</h1>
            <p>Manage your friends, requests, and connect with new people.</p>
        </div>

        <!-- FRIEND REQUESTS -->
        <div class="table-card">
            <div class="table-header">
                <h2>Friend Requests</h2>
            </div>

            <div class="friends-list">

                <?php
                $stmt = $conn->prepare("
                SELECT * FROM isfriend 
                WHERE status = 'pending' 
                AND action_user != ? 
                AND (user_one = ? OR user_two = ?)
            ");

                $stmt->bind_param(
                    "sss",
                    $_SESSION['username'],
                    $_SESSION['username'],
                    $_SESSION['username']
                );

                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    echo '<div class="empty-state">No pending friend requests.</div>';
                }

                while ($row = $result->fetch_assoc()) {

                    $sender = ($row['user_one'] === $_SESSION['username'])
                        ? $row['user_two']
                        : $row['user_one'];
                ?>

                    <div class="friend-card">

                        <div class="friend-info">
                            <div class="friend-avatar">
                                <?= strtoupper(substr($sender, 0, 1)) ?>
                            </div>

                            <div>
                                <div class="friend-name">
                                    <?= htmlspecialchars($sender) ?>
                                </div>

                                <div class="friend-subtitle">
                                    Sent you a friend request
                                </div>
                            </div>
                        </div>

                        <div class="friend-actions">
                            <form method="POST">
                                <input type="hidden" name="u1" value="<?= $row['user_one'] ?>">
                                <input type="hidden" name="u2" value="<?= $row['user_two'] ?>">

                                <button class="btn-success" name="accept">
                                    Accept
                                </button>

                                <button class="btn-danger" name="decline">
                                    Decline
                                </button>
                            </form>
                        </div>

                    </div>

                <?php
                }

                $stmt->close();
                ?>

            </div>
        </div>

        <!-- FRIENDS -->
        <div class="table-card">
            <div class="table-header">
                <h2>Your Friends</h2>
            </div>

            <div class="friends-list">

                <?php
                $stmt = $conn->prepare("
                SELECT * FROM isfriend 
                WHERE status = 'accepted' 
                AND (user_one = ? OR user_two = ?)
            ");

                $stmt->bind_param(
                    "ss",
                    $_SESSION['username'],
                    $_SESSION['username']
                );

                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    echo '<div class="empty-state">You have no friends yet.</div>';
                }

                while ($row = $result->fetch_assoc()) {

                    $friend = ($row['user_one'] === $_SESSION['username'])
                        ? $row['user_two']
                        : $row['user_one'];
                ?>

                    <div class="friend-card">

                        <div class="friend-info">

                            <div class="friend-avatar">
                                <?= strtoupper(substr($friend, 0, 1)) ?>
                            </div>

                            <div>
                                <div class="friend-name">
                                    <?= htmlspecialchars($friend) ?>
                                </div>

                                <div class="friend-subtitle">
                                    Friends
                                </div>
                            </div>

                        </div>

                        <div class="friend-actions">
                            <form method="POST">

                                <input type="hidden" name="u1" value="<?= $row['user_one'] ?>">
                                <input type="hidden" name="u2" value="<?= $row['user_two'] ?>">

                                <button class="btn-danger" name="unfriend">
                                    Unfriend
                                </button>

                            </form>
                        </div>

                    </div>

                <?php
                }

                $stmt->close();
                ?>

            </div>
        </div>

        <!-- ADD FRIEND -->
        <div class="form-card">
            <h2>Add Friend</h2>

            <form method="POST">

                <div class="form-group">
                    <label>Username</label>

                    <input
                        type="text"
                        name="search_user"
                        placeholder="Search username..."
                        required>
                </div>

                <button type="submit">
                    Search User
                </button>

            </form>

            <?php
            if (isset($_POST['search_user'])) {

                $stmt = $conn->prepare("
                SELECT pk_username 
                FROM users
                WHERE pk_username = ?
            ");

                $stmt->bind_param("s", $_POST['search_user']);
                $stmt->execute();

                $result = $stmt->get_result();

                if ($row = $result->fetch_assoc()) {

                    if ($row['pk_username'] !== $_SESSION['username']) {
            ?>

                        <div class="search-result">

                            <div class="friend-info">

                                <div class="friend-avatar">
                                    <?= strtoupper(substr($row['pk_username'], 0, 1)) ?>
                                </div>

                                <div>
                                    <div class="friend-name">
                                        <?= htmlspecialchars($row['pk_username']) ?>
                                    </div>

                                    <div class="friend-subtitle">
                                        User found
                                    </div>
                                </div>

                            </div>

                            <form method="POST">

                                <button
                                    class="btn-success"
                                    name="send_request"
                                    value="<?= $row['pk_username'] ?>">
                                    Send Friend Request
                                </button>

                            </form>

                        </div>

            <?php
                    }
                } else {

                    echo '<div class="empty-state">User not found.</div>';
                }

                $stmt->close();
            }
            ?>

        </div>

    </div>

</body>

</html>