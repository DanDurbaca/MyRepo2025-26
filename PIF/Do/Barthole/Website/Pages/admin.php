<?php
session_start();
require "../Libraries/loginlib.php";
require "../Libraries/navbar.php";

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

$user = null;
$error = null;

if (isset($_GET['clear'])) {
    header("Location: admin.php");
    exit;
}

if (!empty($_GET['username'])) {
    $stmt = $conn->prepare("
        SELECT pk_username, firstName, lastName, email, role
        FROM users WHERE pk_username = ?
    ");
    $stmt->bind_param("s", $_GET['username']);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
    } else {
        $error = "User not found.";
    }
}

if (isset($_POST['action'])) {
    switch ($_POST['action']) {

        case 'toggle_admin':
            if ($_POST['username'] !== $_SESSION['username']) {
                $stmt = $conn->prepare("
                    UPDATE users SET role = IF(role='Admin','User','Admin')
                    WHERE pk_username = ?
                ");
                $stmt->bind_param("s", $_POST['username']);
                $stmt->execute();
            }
            break;

        case 'delete_user':
            if ($_POST['username'] !== $_SESSION['username']) {
                $conn->begin_transaction();

                $stmt = $conn->prepare("
                    DELETE m FROM measurements m
                    JOIN stations s ON s.pk_serialNumber = m.fk_station_records
                    WHERE s.fk_user_owns = ?
                ");
                $stmt->bind_param("s", $_POST['username']);
                $stmt->execute();

                $stmt = $conn->prepare("DELETE FROM stations WHERE fk_user_owns = ?");
                $stmt->bind_param("s", $_POST['username']);
                $stmt->execute();

                $stmt = $conn->prepare("DELETE FROM users WHERE pk_username = ?");
                $stmt->bind_param("s", $_POST['username']);
                $stmt->execute();

                $conn->commit();
            }
            break;

        case 'delete_station':
            $stmt = $conn->prepare("DELETE FROM stations WHERE pk_serialNumber = ?");
            $stmt->bind_param("s", $_POST['serial']);
            $stmt->execute();
            break;

        case 'delete_measurement':
            $stmt = $conn->prepare("DELETE FROM measurements WHERE pk_measurement = ?");
            $stmt->bind_param("i", $_POST['mid']);
            $stmt->execute();
            break;

        case 'delete_collection':
            $stmt = $conn->prepare("DELETE FROM collections WHERE pk_collection = ?");
            $stmt->bind_param("i", $_POST['cid']);
            $stmt->execute();
            break;
        case 'create_station':
            $stmt = $conn->prepare("
        INSERT INTO stations (pk_serialNumber, name, description, fk_user_owns)
        VALUES (?, ?, ?, ?)
    ");
            $stmt->bind_param(
                "ssss",
                $_POST['serial'],
                $_POST['name'],
                $_POST['description'],
                $_POST['owner']
            );
            $stmt->execute();
            break;
    }

    $username = $_POST['username'] ?? $_GET['username'] ?? '';
    header("Location: admin.php?username=" . urlencode($username));
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../Styles/styles.css">
    <script src="../Libraries/JS/jquery-3.7.1.min.js"></script>
</head>

<script>
    function updateUserField(input) {
        $.post("../Libraries/updateuserinfo.php", {
            username: "<?= htmlspecialchars($user['pk_username'] ?? '') ?>",
            field: input.dataset.field,
            value: input.value
        });
    }

    function updateStationField(input) {
        $.post("../Libraries/updatestationinfo.php", {
            serial: input.dataset.serial,
            field: input.dataset.field,
            value: input.value
        });
    }
</script>

<body class="light-theme">

    <?php createnavbar("Admin") ?>

    <h1>Admin Panel</h1>

    <form class="form-card admin-search" method="GET">
        <h2>Find User</h2>
        <div class="form-group">
            <input required name="username" placeholder="Exact username"
                value="<?= htmlspecialchars($_GET['username'] ?? '') ?>">
        </div>
        <div class="admin-actions">
            <button>Load User</button>
            <button name="clear" value="1">Clear</button>
        </div>
    </form>

    <?php if ($error) { ?>
        <p style="color:red"><?= htmlspecialchars($error) ?></p>
    <?php } ?>
    <?php if ($user) { ?>
        <div class="admin-card">
            <h2>User: <b><?= htmlspecialchars($user['pk_username']) ?></b></h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input value="<?= htmlspecialchars($user['pk_username']) ?>" disabled></td>
                        <td>
                            <div class="name-inline">
                                <input value="<?= htmlspecialchars($user['firstName']) ?>"
                                    data-field="firstName"
                                    onblur="updateUserField(this)">

                                <input value="<?= htmlspecialchars($user['lastName']) ?>"
                                    data-field="lastName"
                                    onblur="updateUserField(this)">
                            </div>
                        </td>
                        <td><input value="<?= htmlspecialchars($user['email']) ?>" data-field="email" onblur="updateUserField(this)"></td>
                        <td><span class="role-pill"><?= htmlspecialchars($user['role']) ?></span></td>
                        <td class="action-cell">
                            <?php if ($user['pk_username'] !== $_SESSION['username']) { ?>
                                <form method="POST">
                                    <input type="hidden" name="username" value="<?= $user['pk_username'] ?>">
                                    <button name="action" value="toggle_admin">Toggle Admin</button>
                                </form>
                                <form method="POST">
                                    <input type="hidden" name="username" value="<?= $user['pk_username'] ?>">
                                    <button name="action" value="delete_user"
                                        onclick="return confirm('Delete user and ALL data?')">
                                        Delete
                                    </button>
                                </form>
                                <?php } else { ?>You<?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5">
                            <?php
                            $stmt = $conn->prepare("
                                SELECT pk_serialNumber, name, description, fk_user_owns
                                FROM stations WHERE fk_user_owns = ?
                            ");
                            $stmt->bind_param("s", $user['pk_username']);
                            $stmt->execute();
                            $stations = $stmt->get_result();
                            ?>

                            <h3>Stations</h3>
                            <?php if ($stations->num_rows === 0) { ?>
                                <p>No stations found.</p>
                            <?php } else { ?>
                                <table class="admin-table inner-table">
                                    <thead>
                                        <tr>
                                            <th>Serial Number</th>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($station = $stations->fetch_assoc()) { ?>
                                            <tr>
                                                <td>
                                                    <input value="<?= htmlspecialchars($station['pk_serialNumber']) ?>" disabled>
                                                </td>
                                                <td>
                                                    <input value="<?= htmlspecialchars($station['name']) ?>"
                                                        data-serial="<?= htmlspecialchars($station['pk_serialNumber']) ?>"
                                                        data-field="name"
                                                        onblur="updateStationField(this)">
                                                </td>
                                                <td>
                                                    <input value="<?= htmlspecialchars($station['description']) ?>"
                                                        data-serial="<?= htmlspecialchars($station['pk_serialNumber']) ?>"
                                                        data-field="description"
                                                        onblur="updateStationField(this)">
                                                </td>
                                                <td class="action-cell">
                                                    <form method="POST">
                                                        <input type="hidden" name="serial" value="<?= htmlspecialchars($station['pk_serialNumber']) ?>">
                                                        <button name="action" value="delete_station"
                                                            onclick="return confirm('Delete station and ALL associated data?')">
                                                            Delete
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        <form method="POST" class="form-card">
                                            <h4>Create Station</h4>
                                            <input required name="serial" placeholder="Serial Number">
                                            <input name="name" placeholder="Station Name">
                                            <input name="description" placeholder="Description">
                                            <input required name="owner" placeholder="Owner Username">
                                            <button name="action" value="create_station">Create</button>
                                        </form>

                                    </tbody>
                                </table>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5">
                            <?php
                            $stmt = $conn->prepare("
                                SELECT pk_collection, name, description, fk_user_creates
                                FROM collections WHERE fk_user_creates = ?
                            ");
                            $stmt->bind_param("s", $user['pk_username']);
                            $stmt->execute();
                            $collections = $stmt->get_result();
                            ?>

                            <h3>Collections</h3>
                            <?php if ($collections->num_rows === 0) { ?>
                                <p>No collections found.</p>
                            <?php } else { ?>
                                <table class="admin-table inner-table">
                                    <thead>
                                        <tr>
                                            <th>Collection ID</th>
                                            <th>Name</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($collection = $collections->fetch_assoc()) { ?>
                                            <tr>
                                                <td><?= htmlspecialchars($collection['pk_collection']) ?></td>
                                                <td><input value="<?= htmlspecialchars($collection['name']) ?>"></td>
                                                <td class="action-cell">
                                                    <form method="POST">
                                                        <input type="hidden" name="cid" value="<?= htmlspecialchars($collection['pk_collection']) ?>">
                                                        <button name="action" value="delete_collection" onclick="return confirm('Delete collection?')">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5">
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM measurements m JOIN stations s ON s.pk_serialNumber = m.fk_station_records WHERE s.fk_user_owns = ?");
                            $stmt->bind_param("s", $user['pk_username']);
                            $stmt->execute();
                            $measurements = $stmt->get_result();
                            ?>
                            <h3>Measurements</h3>
                            <?php if ($measurements->num_rows === 0) { ?>
                                <p>No measurements found.</p>
                            <?php } else { ?>
                                <table class="admin-table inner-table">
                                    <thead>
                                        <tr>
                                            <th>Measurement ID</th>
                                            <th>Station Serial</th>
                                            <th>Temperature</th>
                                            <th>Humidity</th>
                                            <th>Pressure</th>
                                            <th>Light</th>
                                            <th>Gas</th>
                                            <th>Timestamp</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($measurement = $measurements->fetch_assoc()) { ?>
                                            <tr>
                                                <td><?= htmlspecialchars($measurement['pk_measurement']) ?></td>
                                                <td><?= htmlspecialchars($measurement['fk_station_records']) ?></td>
                                                <td><?= htmlspecialchars($measurement['temperature']); ?></td>
                                                <td><?= htmlspecialchars($measurement['humidity']); ?></td>
                                                <td><?= htmlspecialchars($measurement['pressure']); ?></td>
                                                <td><?= htmlspecialchars($measurement['light']); ?></td>
                                                <td><?= htmlspecialchars($measurement['gas']); ?></td>
                                                <td><?= htmlspecialchars($measurement['timestamp']); ?></td>
                                                <td><?= htmlspecialchars($measurement['fk_station_records']); ?></td>
                                                <td><?= htmlspecialchars($measurement['timestamp']) ?></td>
                                                <td class="action-cell">
                                                    <form method="POST">
                                                        <input type="hidden" name="mid" value="<?= htmlspecialchars($measurement['pk_measurement']) ?>">
                                                        <button name="action" value="delete_measurement"
                                                            onclick="return confirm('Delete measurement?')">
                                                            Delete
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            <?php } ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php } ?>
    <?php if (!$user) { ?>
        <form class="form-card" method="POST">
            <h2>Create User</h2>
            <div class="form-group"><input required name="username" placeholder="Username"></div>
            <div class="form-group"><input required name="firstName" placeholder="First name"></div>
            <div class="form-group"><input required name="lastName" placeholder="Last name"></div>
            <div class="form-group"><input required type="email" name="email" placeholder="Email"></div>
            <div class="form-group"><input required type="password" name="password" placeholder="Password"></div>
            <div class="form-group"><input required type="password" name="confirmPassword" placeholder="Confirm password"></div>
            <button name="createaccount">Create Account</button>
        </form>
    <?php } ?>

</body>

</html>