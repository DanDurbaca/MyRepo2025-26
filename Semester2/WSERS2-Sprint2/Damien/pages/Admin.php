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
        $sqlQuery->bind_param("sssss", $_POST["PNAME"], $_POST["LinkIMG"], $_POST["Price"], $_POST["ENDescription"],  $_POST["DEDescription"]);
        $sqlQuery->execute();
        $result = $sqlQuery->get_result();
    }
    if ($bShowForm) {
    ?>
        <form class=Register method="POST">
            <div><?= $arrayTranslation["ProductNameID"] ?></div>
            <input type="text" name="PNAME">
            <div><?= $arrayTranslation["ImageLinkID"] ?></div>
            <input type="text" name="LinkIMG">
            <div><?= $arrayTranslation["PriceID"] ?></div>
            <input type="text" name="Price">
            <div><?= $arrayTranslation["ENDescriptionID"] ?></div>
            <input type="text" name="ENDescription">
            <div><?= $arrayTranslation["DEDescriptionID"] ?></div>
            <input type="text" name="DEDescription">
            <input type="submit" value="<?= $arrayTranslation["CreateBtn"] ?>">
        </form>

    <?php
    }
    ?>
    <br>
    <br>
    <h1><?= $arrayTranslation["OrderHistoryLable"] ?></h1>
    <table class="cart">
        <tr>
            <th><?= $arrayTranslation["OrderID"] ?></th>
            <th><?= $arrayTranslation["Client"] ?></th>
            <th><?= $arrayTranslation["ContentsLable"] ?></th>
            <th><?= $arrayTranslation["StatusID"] ?></th>
        </tr>
        <?php
        $sqlQuery = $connection->prepare("select o.orderid, c.username, o.statusEN, o.statusDE from orders o join clients c on o.username=c.username order by o.orderid desc");
        $sqlQuery->execute();
        $result = $sqlQuery->get_result();
        while ($row = $result->fetch_assoc()) {
        ?>
            <tr>
                <td><?= $row["orderid"] ?></td>
                <td><?= $row["username"] ?></td>
                <td>
                    <table><?php
                            $sqlSubQuery = $connection->prepare("select p.ProductNameEN, bi.quantity from orders o join boughtitem bi on o.orderid=bi.orderid join products p on bi.ProductNameEN=p.ProductNameEN where o.orderid=?");
                            $sqlSubQuery->bind_param("i", $row["orderid"]);
                            $sqlSubQuery->execute();
                            $subResult = $sqlSubQuery->get_result();
                            while ($subRow = $subResult->fetch_assoc()) {
                            ?>
                            <tr>
                                <td><?= $subRow["ProductNameEN"] ?></td>
                                <td><?= $subRow["quantity"] ?></td>
                            </tr>
                        <?php
                            }
                        ?>
                    </table>
                </td>
                <td><?= $row[($language == "EN") ? "statusEN" : "statusDE"] ?></td>
                <?php if ($row["statusEN"] != "Delivered") { ?>
                    <td>
                        <form method="POST"><input type="hidden" value="<?= $row["orderid"] ?>" name="orderToSend"></input><input type="submit" value="<?= $arrayTranslation["SendBtn"] ?>"></form>
                    </td>
                <?php } ?>
            </tr>
        <?php
        }
        ?>
    </table>

</body>

</html>