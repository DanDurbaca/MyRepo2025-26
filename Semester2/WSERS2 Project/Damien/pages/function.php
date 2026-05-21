<?php

session_start();

$connection = new mysqli("localhost", "root", "","daxda083");
if(isset($_SESSION["Cart"])){

}else{
  $_SESSION["Cart"] =[];
}

if (isset($_POST["itemToBuy"], $_POST["quantityToBuy"])){
  $item = $_POST["itemToBuy"];
  if (isset($_SESSION["Cart"][$item])){
    $_SESSION["Cart"][$item] = $_SESSION["Cart"][$item] + $_POST["quantityToBuy"];
  } else{
    $_SESSION["Cart"][$item] = $_POST["quantityToBuy"];
  }
}
if (isset($_POST["itemToDelete"])){
  $item = $_POST["itemToDelete"];
  unset($_SESSION["Cart"][$item]);
}

if (isset($_POST['logout'])) {
  session_unset();
  session_destroy();
  session_start();
  header("refresh:0; url=index.php?lang=$language");
}

if (!isset($_SESSION['Userlogged'])) {
  $_SESSION['Userlogged'] = "false";
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
        if ($_SESSION['Userlogged'] == "false") {
        ?>
          <li> <a <?= ($caller == "Register") ? "class='highlight'" : ""; ?>href="Register.php?lang=<?= $language ?>"><?= $arrayTranslation["RegisterBtn"] ?></a></li>
          <li> <a <?= ($caller == "Log in") ? "class='highlight'" : ""; ?>href="LOGIN.php?lang=<?= $language ?>"><?= $arrayTranslation["LOGINBtn"] ?></a></li>
        <?php
        } else {
        ?>
    
        <li> <a <?= ($caller == "Forum") ? "class='highlight'" : ""; ?>href="Forum.php?lang=<?= $language ?>">Forum</a></li>
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
          }else {
            $quantity=0;
            foreach ($_SESSION["Cart"] as $itemID => $itemQuantity){
              $quantity += $itemQuantity;
            }
            ?>
            <li> <a <?= ($caller == "ShoppingCart") ? "class='highlight'" : ""; ?> href="ShoppingCart.php?lang=<?= $language ?>"><?= $arrayTranslation["CartBTN"] ?> [<?= $quantity ?>]</a></li>
          <?php
          }
          ?>

          <form method="post" style="display: inline;">
            <input type="submit" value="<?= $arrayTranslation["LOGOUTBTN"] ?>" name="logout">
          </form>
        <?php
        }
        ?>
        <form class="changeLANG">
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
$connection = new mysqli("localhost", "root", "", "Daxda083");
$sqlQuery = $connection->prepare("select * from translation");
$sqlQuery->execute();
$result = $sqlQuery->get_result();
while ($row = $result->fetch_assoc()) {
  $arrayTranslation[$row["transKey"]] = ($language == "EN") ? trim($row["Englishtext"]) : trim($row["Germantext"]);
}


//print("The current language is: " . $language);

?>








<?php
function userAlreadyRegistered($chekedUser)
{
  global $connection;
  $bReturnValue = false;
  $sqlQuery = $connection->prepare("select * from clients");
  $sqlQuery->execute();
  $result = $sqlQuery->get_result();

  while ($row = $result->fetch_assoc()) {
    if ($row["username"] == $chekedUser)
      $bReturnValue = true;
  }

  return $bReturnValue;
}
?>