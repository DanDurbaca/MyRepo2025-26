<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php
    $pplDB = new mysqli("127.0.0.1", "root", "", "ppl");
    session_start();
    $co = $pplDB->query("SELECT * FROM countries;");
    if (isset($_GET['countries'])) {
        $_SESSION['country'] = $_GET['countries'];
    }
    $_SESSION['country'] = isset($_SESSION['country']) ? $_SESSION['country'] : "defco";

    ?>
    <form id="co" method="get">
        <select name="countries" onchange="this.form.submit()">
            <option value="defco" <?php if ($_SESSION['country'] === "defco") {
                print "selected";
            } ?>>Please select a
                country
            </option>
            <?php while ($r = $co->fetch_assoc()) { ?>
                <option value="<?php print $r['CountryId']; ?>" <?php if ($_SESSION['country'] == $r['CountryId']) {
                       print "selected";
                   } ?>><?php print $r['CountryName']; ?></option>
            <?php } ?>
        </select>
    </form>

    <?php
    $ci = $pplDB->query("SELECT * FROM cities;");
    if (isset($_GET['cities'])) {
        $_SESSION['city'] = $_GET['cities'];
    }
    $_SESSION['city'] = isset($_SESSION['city']) ? $_SESSION['city'] : "defci";
    if ($_SESSION['country'] != 'defco') {
        ?>
        <form id="ci" method="get">
            <select name="cities" onchange="this.form.submit()">
                <option value="defci" <?php if ($_SESSION['city'] === "defci") {
                    print "selected";
                } ?>>Please select a city
                </option>
                <?php while ($r = $ci->fetch_assoc()) {
                    if ($r['CountryId'] == $_SESSION['country']) { ?>
                        <option value="<?php print $r['CityId']; ?>" <?php if ($_SESSION['city'] == $r['CityId']) {
                               print "selected";
                           } ?>><?php print $r['CityName']; ?>
                        </option>
                    <?php }
                } ?>
            </select>
        </form>
    <?php } ?>

    <?php
    if ($_SESSION['city'] != 'defci' && $_SESSION['country'] != 'defco') {
        $_SESSION['nameOrder'] = isset($_GET['nameOrder']) ? $_GET['nameOrder'] : "defNOrder";
        ?>

        <form method="get">
            <select name="nameOrder" onchange="this.form.submit()">
                <option value="defNOrder" <?php if ($_SESSION['nameOrder'] === "defNOrder") {
                    print "selected";
                } ?>>No order
                </option>
                <option value="ASC" <?php if ($_SESSION['nameOrder'] === "ASC") {
                    print "selected";
                } ?>>ASC
                </option>
                <option value="DES" <?php if ($_SESSION['nameOrder'] === "DES") {
                    print "selected";
                } ?>>DES
                </option>
            </select>
        </form>


        <?php
        $_SESSION['ageOrder'] = isset($_GET['ageOrder']) ? $_GET['ageOrder'] : "defAOrder";
        ?>

        <form method="get">
            <select name="ageOrder" onchange="this.form.submit()">
                <option value="defAOrder" <?php if ($_SESSION['ageOrder'] === "defAOrder") {
                    print "selected";
                } ?>>No order
                </option>
                <option value="ASC" <?php if ($_SESSION['ageOrder'] === "ASC") {
                    print "selected";
                } ?>>ASC
                </option>
                <option value="DES" <?php if ($_SESSION['ageOrder'] === "DES") {
                    print "selected";
                } ?>>DES
                </option>
            </select>
        </form>

        <?php
        $str = "SELECT * FROM ppl";
        if ($_SESSION['nameOrder'] == "ASC") {
            $str += " order by PersonName";
        }
        if ($_SESSION['nameOrder'] == "DES") {
            $str += " order by PersonName desc";
        }
        $str += ";";
        $ppl = $pplDB->query($str);
        ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Age</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($r = $ppl->fetch_assoc()) {
                    if ($_SESSION['city'] == $r['CityId']) {
                        ?>
                        <tr>
                            <td>
                                <?php print $r['PersonId']; ?>
                            </td>
                            <td>
                                <?php print $r['PersonName']; ?>
                            </td>
                            <td>
                                <?php print $r['Age']; ?>
                            </td>
                        </tr>
                    <?php }
                } ?>
            </tbody>
        </table>
        <?php
    }
    ?>
</body>

</html>