<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="ShopStyles.css?<?= time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>

    <?php
    include_once("CommonCode.php");
    NavigationBar("Contact");
    ?>

    <h1>Contact Us</h1>
    <p>This is our contact information...</p>
    <ul>
        <li>Email: info@example.com</li>
        <li>Phone: +1 234 567 890</li>
        <li>Address: 123 Main Street, City, Country</li>
    </ul>
    <form method="POST">
        <div>First name:</div>
        <input type="text" name="fname">
        <div>Your password:</div>
        <input type="password" name="psw">
        <input type="submit" value="Send this data">
    </form>
    <?php
    /*
    $_GET 
    is an array that contains all the filled in form data by the user

    we need to check what is inside this array:
    we will use (for debug): 
        var_dump($_GET)
    */
    //print($_GET);
    var_dump($_GET);
    var_dump($_POST);

    if (isset($_POST["fname"])) {
    ?>
        <h1>Welcome to our website <?= $_POST["fname"] ?></h1>
    <?php
    }
    ?>

</body>

</html>