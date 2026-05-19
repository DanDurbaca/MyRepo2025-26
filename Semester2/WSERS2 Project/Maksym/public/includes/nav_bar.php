<?php
  function nav_bar(array $opts) {
    $default = [
      "languages" => [],
      "current-lang" => "en",
      "logo" => "../../assets/images/logo/logo.png",
      "profile-pic" => "../../assets/images/profile/empty-profile.webp",
      "cart" => "../../assets/images/cart/Cart.webp",

      "pages" => [
        "home" => "/",
        "market" => "../market",
        "contact" => "../contact",
        "forum" => "../forum",
        "add-product" => "../add-product",
        "cart" => "../cart"
      ]
    ];

    $activePage = substr($_SERVER["PHP_SELF"], 28);
?>

    <div id="nav-bar">
      <div id="logo"><a href="<?= $opts["pages"]["home"] ?? $default["pages"]["home"] ?>"><img src=<?= $opts["logo"] ?? $default["logo"] ?> alt="logo" /></a></div>

      <div id="nav-list">
        <ul>
          <li id="<?= $activePage === "/home/index.php" ? "active-page" : "" ?>"><a href="<?= $opts["pages"]["home"] ?? $default["pages"]["home"] ?>" ><?= t("navigation.nav-home");?></a></li>
          <li id="<?= $activePage === "/market/index.php" ? "active-page" : "" ?>"><a href="<?= $opts["pages"]["market"] ?? $default["pages"]["market"] ?>"><?= t("navigation.nav-market");?></a></li>
          <li id="<?= $activePage === "/contact/index.php" ? "active-page" : "" ?>"><a href="<?= $opts["pages"]["contact"] ?? $default["pages"]["contact"] ?>"><?= t("navigation.nav-contact"); ?></a></li>

          <?php if ($_SESSION["isLogged"]) { ?>
            <li id="<?= $activePage === "/forum/index.php" ? "active-page" : "" ?>"><a href="<?= $opts["pages"]["forum"] ?? $default["pages"]["forum"] ?>">Forum</a></li>
          <?php } ?>

          <?php if ($_SESSION["isAdmin"]) { ?>
            <li id="<?= $activePage === "/add-product/index.php" ? "active-page" : "" ?>"><a href="<?= $opts["pages"]["add-product"] ?? $default["pages"]["add-product"] ?>">Add Prod</a></li>
          <?php } ?>
        </ul>
      </div>

      <div id="localisation">
        <form method="GET">
          <select name="lang" onchange="this.form.submit()">
            <?php foreach ($opts["languages"] ?? $default["languages"] as $lang): ?>
              <option value="<?= htmlspecialchars($lang["code"]) ?>" <?= $opts["current-lang"] === $lang["code"] ?? $default["current-lang"] === $lang["code"] ? "selected" : "" ?>>
              <?= htmlspecialchars($lang['name']) ?>
              </option>
            <?php endforeach;?>
          </select>
        </form>
      </div>

      <?php if (!$_SESSION["isLogged"]) { ?>

        <div id="registration">
          <button class="reg-button" type="button"><?= t("nav-bar.sign-up");?></button>
          <div>/</div>
          <button class="reg-button" type="button"><?= t("nav-bar.sign-in");?></button>
        </div>

      <?php } else { ?>
        <?php if (!$_SESSION["isAdmin"]) { ?>
        <div id="cart">
          <div id="cart-icon"><a href="<?= $default["pages"]["cart"] ?>"><img src="<?= $default["cart"] ?>" alt="cart" /></a></div>
          <div id="product-num"><p><?= empty($_SESSION["Cart"]) ? 0 : count($_SESSION["Cart"]) ?></p></div>
        </div>
        <?php } ?>

        <div id="profile-picture"><img src="<?= $opts["profile-pic"] ?? $default["profile-pic"] ?>" alt="profile-pic" /></div>
        <div><?= $_SESSION["username"] ?></div>
      <?php }?>

      <div id="drop-down">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
      </div>
    </div>


<?php
  }
?>
