<?php
include_once("ccode.php");
include_once("navbar.php");
navbar($tArray["ProductBtn"]);
?>
<!doctype html>
<html lang="en">

<head>
    <link rel="stylesheet" href="style.css? <?= time(); ?> ">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Products - Mentorship Shop</title>
</head>

<body>
    <script src="script.js?<?= time(); ?>"></script>

    <main class="page">
        <h1><?= $tArray["MentorsH1"] ?></h1>

        <div class="products">
            <?php
            $fhandler = fopen("products.csv", "r");
            fgets($fhandler);

            while (!feof($fhandler)) {
                $line = fgets($fhandler);
                $cols = explode(";", $line);
                if (count($cols) === 6) {
                    ?>
                    <div class="prod">
                        <div>
                            <?php $lang == "EN" ? print $cols[0] : print $cols[1]; ?>
                        </div>

                        <img src="pics/<?php print $cols[2]; ?>"
                            alt="<?php $lang == "EN" ? print $cols[0] : print $cols[1]; ?>">

                        <div>
                            <?php print $cols[3]; ?>
                        </div>

                        <div>
                            <span class="price">
                                <?php $lang == "EN" ? print $cols[4] : print $cols[5]; ?>
                            </span>
                        </div>
                    </div>
                <?php }
            } ?>
        </div>
    </main>
</body>

</html>