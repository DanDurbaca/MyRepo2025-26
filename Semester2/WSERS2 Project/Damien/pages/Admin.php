<?php
include_once("function.php");
if ($_SESSION['UserType'] != "admin") {
    die("Access denied");
}
?>


<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <link rel="stylesheet" href="style.css?<?= time() ?>">
    <meta charset="utf-8">
    <title>Register</title>
</head>

<body class="restBG">

    <?php
    NavigationBar("Register");
    ?>
    <H1>
        <?= $arrayTranslation["Passwordlable"] ?>
    </H1>
    <?php
    $bShowForm = true;
    if (isset($_POST["PNAME"], $_POST["LinkIMG"], $_POST["Price"], $_POST["ENDescription"], $_POST["DEDescription"])) {
        $bShowForm = false;
        print("Its going to be added soon:");
        $sqlQuery = $connection->prepare("INSERT INTO products(ProductNameEN, ImageLink, Price, DescriptionEN, DescriptionDE) values(?,?,?,?,?);");
         $sqlQuery->bind_param("sssss", $_POST["PNAME"],$_POST["LinkIMG"] , $_POST["Price"], $_POST["ENDescription"],  $_POST["DEDescription"] );
        $sqlQuery->execute();
        $result = $sqlQuery->get_result();

      
    }
    if ($bShowForm) {
    ?>
        <form class=Register method="POST">
            <div>Product Name:</div>
            <input type="text" name="PNAME">
            <div>IMG Link:</div>
            <input type="text" name="LinkIMG">
            <div>Price:</div>
            <input type="text" name="Price">
            <div>EN Description:</div>
            <input type="text" name="ENDescription">
            <div>DE Description:</div>
            <input type="text" name="DEDescription">
            <input type="submit" value=Create>
        </form>

    <?php
    }
    ?>


</body>

</html>