<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../Styles/styles.css">
</head>

<body class="light-theme">
        <?php
        include("../Libraries/navbar.php");
        createnavbar("Logout");
        ?>

        <h1>Logout</h1>
        <p>You are about to log out. Are you sure you want to do this?</p>
        <form method="POST">
            <input type="submit" value="Yes, log me out">
        </form>

        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            session_destroy();
            session_abort();
            header("Location:index.php");
        }
        ?>
    </body>

</html>