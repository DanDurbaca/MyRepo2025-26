<?php
include_once "ccode.php";
include_once "navbar.php";
if (!$_SESSION["canAddProducts"]) {
    header("Location: welcome.php?lang=" . $lang);
    exit;
}
navbar($tArray["addPBtn"]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="style.css? <?= time(); ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php
    if (isset($_POST['PnameEN'], $_POST['PnameUA'], $_POST['Pprice'], $_POST['PdescriptionEN'], $_POST['PdescriptionUA'])) {
        validate_product_and_add($_POST['PnameEN'], $_POST['PnameUA'], $_POST['Pprice'], $_POST['PdescriptionEN'], $_POST['PdescriptionUA']);
    }
    ?>
    <main class="page">
        <section class="contact">
            <h2><?= $tArray["addPdctH"] ?></h2>
            <form method="post">
                <label>
                    <?= $tArray["pnameENInput"] ?>
                    <input type="text" name="PnameEN" required>
                </label>
                <label>
                    <?= $tArray["pnameUAInput"] ?>
                    <input type="text" name="PnameUA" required>
                </label>
                <label>
                    <?= $tArray["priceInput"] ?>
                    <input placeholder='<?= $tArray["priceHelper"] ?>' type="text" name="Pprice" required>
                </label>
                <label>
                    <?= $tArray["descENInput"] ?>
                    <textarea name="PdescriptionEN" required></textarea>
                </label>
                <label>
                    <?= $tArray["descUAInput"] ?>
                    <textarea name="PdescriptionUA" required></textarea>
                </label>
                <button type="submit"><?= $tArray["addPBtn"] ?></button>
            </form>
        </section>
    </main>
</body>

</html>