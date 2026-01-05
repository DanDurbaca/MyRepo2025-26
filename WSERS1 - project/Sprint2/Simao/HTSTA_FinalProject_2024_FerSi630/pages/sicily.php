<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>HTSTA Final Project</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>
    <header>
            <?php
            include_once("commonCode.php");
            navBar("Country1");
            ?>
    </header>
    <main>
        <h2>space</h2>
			<h1><a href="https://en.wikipedia.org/wiki/Sicily" target="blank_"><?= $arrayOfTranslations["SicilyTitle"]  ?></a></h1>
		<h2>space</h2>

        <h1><a href="https://en.wikipedia.org/wiki/Sicily" target="_blank"></a></h1>

        <a href="../images/sicily.jpg"><img src="../images/sicily.jpg" alt="Sicily" style="width: 50%"></a>

        <p>
            <?= $arrayOfTranslations["SicilyDescription"]  ?>
        </p>

        <p>
            <?= $arrayOfTranslations["Recommendations"]  ?>
        </p>

        <dl>
            <dt><a href="https://en.wikipedia.org/wiki/Palermo_Cathedral" target="_blank"><?= $arrayOfTranslations["Palermo"]  ?></a></dt>
            <dt><a href="https://en.wikipedia.org/wiki/Ancient_theatre_of_Taormina" target="_blank"><?= $arrayOfTranslations["Taormina"]  ?></a></dt>
            <dt><a href="https://en.wikipedia.org/wiki/Palazzo_dei_Normanni" target="_blank"><?= $arrayOfTranslations["RoyalPalace"]  ?></a></dt>
        </dl>
    </main>

    <footer>
        <p> LPEM - HTSTA Final Project 2024 </p>
    </footer>
</body>

</html>