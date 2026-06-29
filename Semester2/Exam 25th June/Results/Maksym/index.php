<?php
  require_once __DIR__ . "/bootstrap.php";
?>

<!DOCTYPE html>

<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Exam2</title>
  </head>

  <body>
    <form method="POST">
      <select name="countrs" onchange="this.form.submit()">
        <option value="0" <?= $_SESSION["current_countr"] === 0 ? "selected" : "" ?>>Please select a country</option>

        <?php foreach($_SESSION["countries"] as $country): ?>
          <option value="<?= $country["CountryId"] ?>" <?= $_SESSION["current_countr"] === $country["CountryId"] ? "selected" : "" ?>><?= $country["CountryName"] ?></option>
        <?php endforeach ?>
      </select>
    </form>

    <?php if ($_SESSION["current_countr"] !== 0) { ?>
      <form method="POST">
        <select name="cities" onchange="this.form.submit()">
        <option value="0" <?= $_SESSION["current_city"] === 0 ? "selected" : "" ?>>Please select a city</option>

        <?php foreach($_SESSION["cities"] as $city): ?>
          <option value="<?= $city["CityId"] ?>" <?= $_SESSION["current_city"] === $city["CityId"] ? "selected" : "" ?>><?= $city["CityName"] ?></option>
        <?php endforeach ?>
        </select>
      </form>
    <?php } ?>

    <h2>Ordered by name:</h2>

    <?php if ($_SESSION["current_city"] !== 0) { ?>
      <form method="POST">
        <select name="cities" onchange="this.form.submit()">
          <option value="0" <?= $_SESSION["current_person"] === 0 ? "selected" : "" ?>>Please select a person</option>

          <?php foreach($_SESSION["people_byname"] as $person): ?>
            <option value="<?= $person["PersonId"] ?>" <?= $_SESSION["current_person"] === $person["PersonId"] ? "selected" : "" ?>><?= $person["PersonName"] . " " . $person["Age"]?></option>
          <?php endforeach ?>
        </select>
      </form>
    <?php } ?>

    <h2>Ordered by age:</h2>

    <?php if ($_SESSION["current_city"] !== 0) { ?>
        <form method="POST">
        <select name="cities" onchange="this.form.submit()">
        <option value="0" <?= $_SESSION["current_person"] === 0 ? "selected" : "" ?>>Please select a person</option>

        <?php foreach($_SESSION["people_byage"] as $person): ?>
        <option value="<?= $person["PersonId"] ?>" <?= $_SESSION["current_person"] === $person["PersonId"] ? "selected" : "" ?>><?= $person["PersonName"] . " " . $person["Age"]?></option>
        <?php endforeach ?>
        </select>
        </form>
        <?php } ?>
  </body>

</html>
