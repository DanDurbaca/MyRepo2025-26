<?php
include_once "ccode.php";
include_once "navbar.php";
include_once "deepL/deepL.php";
if (!$_SESSION["canAddProducts"]) {
    header("Location: welcome.php?lang=" . $lang);
    exit;
}
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
    navbar($tArray["addPBtn"]);

    if (isset($_POST["Pname"], $_POST["Pprice"], $_POST["Pdescription"])) {
        $descUA = deepl_translate($_POST["Pdescription"]);
        $nameUA = deepl_translate($_POST["Pname"]);

        if ($nameUA == NULL || $descUA == NULL) {
            return;
        }
        file_put_contents(
            'products.csv',
            "\n" . $_POST["Pname"] . ";" . $nameUA . ";" . "NULL" . ";" . $_POST["Pprice"] . $_POST["Pdescription"] . ";" . $descUA,
            FILE_APPEND | LOCK_EX
        );
    }
    ?>
    <main class="page">
        <section class="contact">
            <h2><?= $tArray["addPdctH"] ?></h2>
            <form method="post">
                <label>
                    <?= $tArray["pnameENInput"] ?>
                    <input type="text" name="Pname" required>
                </label>
                <label>
                    <?= $tArray["priceInput"] ?>
                    <input type="text" name="Pprice" required>
                </label>
                <label>
                    <?= $tArray["descENInput"] ?>
                    <textarea name="Pdescription" required></textarea>
                </label>
                <label>
                    <?= $tArray["imgInput"] ?>
                    <input type="file" name="file" id="file">
                </label>
                <button type="submit"><?= $tArray["addPBtn"] ?></button>
            </form>
        </section>
    </main>
</body>

</html>