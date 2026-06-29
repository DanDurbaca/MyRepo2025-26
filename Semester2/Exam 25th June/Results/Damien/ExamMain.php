<!DOCTYPE html>
<html lang="en" dir="ltr">

<?php
session_start();
?>

<head>
    <link rel="stylesheet" href="style.css?<?= time() ?>">
    <meta charset="utf-8">
    <title>Examdaxda083</title>

</head>
<?php
$DEF = "Please select a country";
if (isset($_GET["lang"])) {
    $DEF = $_GET["lang"];
}
$connection = new mysqli("localhost", "root", "", "ppl");
$sqlQuery = $connection->prepare("select * from Countries");
$sqlQuery->execute();
$result = $sqlQuery->get_result();

?>
<form class="changeCountry">
    <select name=lang onchange="this.form.submit">
        <option value="1" <?php if ($DEF == "France") print "selected"; ?>>France</option>
        <option value="2" <?php if ($DEF == "Germany") print "selected"; ?>>Germany</option>
        <option value="3" <?php if ($DEF == "Romania") print "selected"; ?>>Romania</option>
        <option value="4" <?php if ($DEF == "Italy") print "selected"; ?>>Italy</option>
        <option value="5" <?php if ($DEF == "Spain") print "selected"; ?>>Spain</option>
        <option value="6" <?php if ($DEF == "Luxembourg") print "selected"; ?>>Luxembourg</option>
        <option value="7" <?php if ($DEF == "Poland") print "selected"; ?>>Poland</option>
        <option value="8" <?php if ($DEF == "Netherlands") print "selected"; ?>>Netherlands</option>
        <option value="9" <?php if ($DEF == "Belgium") print "selected"; ?>>Belgium</option>
        <option value="10" <?php if ($DEF == "Portugal") print "selected"; ?>>Portugal</option>
    </select>
</form>