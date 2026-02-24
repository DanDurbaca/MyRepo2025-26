<?php

session_start();



if (isset($_POST['logout'])) {
  session_unset();
  session_destroy();
  session_start();
}

if (!isset($_SESSION['Userlogged'])) {
  $_SESSION['Userlogged'] = false;
  $_SESSION['UserType'] = "user";
}

//var_dump($_SESSION);


function NavigationBar($caller)
{
  global $language;
  global $arrayTranslation;
?>
  <header>
    <nav>
      <ul>
        <li> <a <?= ($caller == "Home") ? "class= 'highlight'" : ""; ?> href="index.php?lang=<?= $language ?>"><?= $arrayTranslation["HomeBtn"] ?></a></li>
        <li> <a <?= ($caller == "Products") ? "class='highlight'" : ""; ?> href="Products.php?lang=<?= $language ?>"><?= $arrayTranslation["ProductsBtn"] ?></a></li>

        <?php
        if ($_SESSION['Userlogged'] == false) {
        ?>
          <li> <a <?= ($caller == "Register") ? "class='highlight'" : ""; ?>href="Register.php?lang=<?= $language ?>"><?= $arrayTranslation["RegisterBtn"] ?></a></li>
          <li> <a <?= ($caller == "Log in") ? "class='highlight'" : ""; ?>href="LOGIN.php?lang=<?= $language ?>"><?= $arrayTranslation["LOGINBtn"] ?></a></li>
        <?php
        } else {
        ?>
          <?php
          if (isset($_SESSION['UserType']) && $_SESSION['UserType'] === "admin") {
          ?>
            <li>
              <a <?= ($caller == "Admin") ? "class='highlight'" : ""; ?>
                href="Admin.php?lang=<?= $language ?>">
                Admin
              </a>
            </li>
          <?php
          }
          ?>

          <form method="post">
            <input type="submit" value="<?= $arrayTranslation["LOGOUTBTN"] ?>" name="logout">
          </form>
        <?php
        }
        ?>

        <form>
          <select name=lang onchange="this.form.submit()">
            <option value="EN" <?php if ($language == "EN") print "selected"; ?>>English</option>
            <option value="GE" <?php if ($language == "GE") print "selected"; ?>>German</option>
          </select>
        </form>
      </ul>
    </nav>
  </header>
<?php
}
?>

<?php
$language = "EN";
if (isset($_GET["lang"])) {
  $language = $_GET["lang"];
}
$arrayTranslation = [];
$fileTranslation = fopen("Translation.csv", "r");
while (!feof($fileTranslation)) {
  $lineFromFile = fgets($fileTranslation);
  $piecesOFTranslation = explode(";", $lineFromFile);
  $arrayTranslation[$piecesOFTranslation[0]] = ($language == "EN") ? trim($piecesOFTranslation[1]) : trim($piecesOFTranslation[2]);
}


//print("The current language is: " . $language);

?>








<?php
function userAlreadyRegistered($chekedUser)
{
  $bReturnValue = false;

  $fHandler = fopen("Clients.csv", "r");
  while (!feof($fHandler)) {
    fgets($fHandler);
    $newLine = fgets($fHandler);
    $items = explode(";", $newLine);
    if ($items[0] == $chekedUser)
      $bReturnValue = true;
  }
  fclose($fHandler);

  return $bReturnValue;
}
?>