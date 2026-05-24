<!DOCTYPE html>
<html>

<head>
    <title>Collections</title>
    <link rel="stylesheet" href="../Styles/styles.css">
</head>

<body class="light-theme">

    <?php
    session_start();

    include("../Libraries/conndb.php");
    include("../Libraries/navbar.php");

    createnavbar("Collections");

    if (!isset($_SESSION['username'])) {
        die("You must be logged in.");
    }

    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        die("Invalid collection ID.");
    }

    $loggedUser = $_SESSION['username'];
    $collection_id = (int) $_GET['id'];

    $stmt = $conn->prepare("
    SELECT *
    FROM collections
    WHERE pk_collection = ?
");
    $stmt->bind_param("i", $collection_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$collection = $result->fetch_assoc()) {
        die("Collection not found.");
    }

    $stationSerial = $collection['fk_station_associated'];
    $isOwner = ($collection['fk_user_creates'] === $loggedUser);

    if ($isOwner && isset($_POST['add_user'])) {

        $username = trim($_POST['username']);

        if ($username !== $loggedUser) {

            $check = $conn->prepare("SELECT pk_username FROM users WHERE pk_username = ?");
            $check->bind_param("s", $username);
            $check->execute();
            $check->store_result();

            if ($check->num_rows === 1) {

                $add = $conn->prepare("
                INSERT INTO hasaccess
                (pkfk_user, pkfk_collection, timestamp)
                VALUES (?, ?, CURRENT_TIMESTAMP)
            ");

                $add->bind_param("si", $username, $collection_id);
                $add->execute();
            } else {
                $error = "User does not exist.";
            }
        } else {
            $error = "You already own this collection.";
        }
    }

    if ($isOwner && isset($_POST['remove_user'])) {

        $username = $_POST['remove_user'];

        $remove = $conn->prepare("
        DELETE FROM hasaccess
        WHERE pkfk_user = ?
        AND pkfk_collection = ?
    ");

        $remove->bind_param("si", $username, $collection_id);
        $remove->execute();
    }

    $access_stmt = $conn->prepare("
    SELECT u.pk_username, u.firstName, u.lastName, h.timestamp
    FROM hasaccess h
    JOIN users u ON u.pk_username = h.pkfk_user
    WHERE h.pkfk_collection = ?
");

    $access_stmt->bind_param("i", $collection_id);
    $access_stmt->execute();
    $access_list = $access_stmt->get_result();
    ?>

    <h1>Collection Details</h1>

    <!-- COLLECTION INFO -->
    <div class="table-card">

        <div class="table-header">
            <h2><?php echo htmlspecialchars($collection['name']); ?></h2>
        </div>

        <div class="collection-meta">

            <div class="meta-block">
                <span class="meta-label">Description</span>
                <p>
                    <?php echo htmlspecialchars($collection['description']); ?>
                </p>
            </div>

            <div class="meta-grid">

                <div class="meta-item">
                    <span class="meta-label">Start Date</span>
                    <span>
                        <?php echo htmlspecialchars($collection['started_at']); ?>
                    </span>
                </div>

                <div class="meta-item">
                    <span class="meta-label">End Date</span>
                    <span>
                        <?php echo htmlspecialchars($collection['ended_at']); ?>
                    </span>
                </div>

                <div class="meta-item">
                    <span class="meta-label">Station</span>
                    <span>
                        <?php echo htmlspecialchars($stationSerial ?? "No station"); ?>
                    </span>
                </div>

            </div>
        </div>

    </div>

    <?php if ($isOwner) { ?>

        <!-- SHARE COLLECTION -->
        <div class="controls-grid">

            <div class="form-card control-card">

                <div class="card-title">
                    <h2>Share Collection</h2>
                    <p>Grant another user access to this collection.</p>
                </div>

                <form method="POST">

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text"
                            name="username"
                            id="username"
                            placeholder="Enter username"
                            required>
                    </div>

                    <button type="submit" name="add_user">
                        Add User
                    </button>

                </form>

                <?php if (isset($error)) { ?>
                    <p class="error-text">
                        <?php echo htmlspecialchars($error); ?>
                    </p>
                <?php } ?>

            </div>

        </div>

        <!-- SHARED USERS -->
        <div class="table-card">

            <div class="table-header">
                <h2>Shared Users</h2>
            </div>

            <?php if ($access_list->num_rows === 0) { ?>

                <div class="empty-state">
                    No users currently have access.
                </div>

            <?php } else { ?>

                <div class="table-wrapper">

                    <table class="measurement-table">

                        <tr>
                            <th>Username</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Granted At</th>
                            <th>Action</th>
                        </tr>

                        <?php while ($user = $access_list->fetch_assoc()) { ?>

                            <tr>

                                <td>
                                    <?php echo htmlspecialchars($user['pk_username']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($user['firstName']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($user['lastName']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($user['timestamp']); ?>
                                </td>

                                <td>

                                    <form method="POST">

                                        <input type="hidden"
                                            name="remove_user"
                                            value="<?php echo htmlspecialchars($user['pk_username']); ?>">

                                        <button type="submit" class="btn-danger">
                                            Remove
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        <?php } ?>

                    </table>

                </div>

            <?php } ?>

        </div>

    <?php } ?>

    <!-- MEASUREMENTS -->
    <div class="table-card">

        <div class="table-header">
            <h2>Measurements</h2>
        </div>

        <?php if ($stationSerial === null) { ?>

            <div class="empty-state">
                No station associated with this collection.
            </div>

        <?php } else { ?>

            <div class="table-wrapper">

                <table class="measurement-table">

                    <tr>
                        <th>Time</th>
                        <th>Temperature</th>
                        <th>Humidity</th>
                        <th>Pressure</th>
                        <th>Light</th>
                        <th>Gas</th>
                        <th>Station</th>
                    </tr>

                    <?php

                    $data_stmt = $conn->prepare("
                    SELECT *
                    FROM measurements
                    WHERE fk_station_records = ?
                    AND timestamp BETWEEN ? AND ?
                    ORDER BY timestamp DESC
                ");

                    $data_stmt->bind_param(
                        "sss",
                        $stationSerial,
                        $collection['started_at'],
                        $collection['ended_at']
                    );

                    $data_stmt->execute();
                    $data_result = $data_stmt->get_result();

                    while ($row = $data_result->fetch_assoc()) {
                    ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($row['timestamp']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['temperature']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['humidity']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['pressure']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['light']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['gas']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['fk_station_records']); ?>
                            </td>

                        </tr>

                    <?php } ?>

                </table>

            </div>

        <?php } ?>

    </div>

</body>

</html>