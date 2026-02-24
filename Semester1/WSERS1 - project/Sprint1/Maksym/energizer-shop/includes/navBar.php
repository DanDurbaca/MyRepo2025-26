<?php
  function NavBar($pathToLogo = "") {
    global $languages;

    $activePage = basename($_SERVER['PHP_SELF'], ".php");
?>

    <div id="nav-bar">
      <div id="logo"><a href="#"><img src=<?=$pathToLogo?> alt="logo" /></a></div>

      <div id="nav-list">
        <ul>
          <li id="<?= $activePage === "index" ? "active-page" : "" ?>"><a href="#" ><?= t("navigation.nav-home");?></a></li>
          <li id="<?= $activePage === "market" ? "active-page" : "" ?>"><a href="#"><?= t("navigation.nav-market");?></a></li>
          <li id="<?= $activePage === "contact-us" ? "active-page" : "" ?>"><a href="#"><?= t("navigation.nav-contact"); ?></a></li>
        </ul>
      </div>

      <div id="localisation">
        <form method="GET">
          <select name="lang" onchange="this.form.submit()">
            <?php foreach ($languages as $lang) {?>
              <option value="<?= htmlspecialchars($lang['code']) ?>" <?= $_SESSION['lang'] === $lang['code'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($lang['name']) ?>
              </option>
            <?php }?>
          </select>
        </form>
      </div>

      <div id="registration">
        <button class="reg-button" type="button"><?= t("nav-bar.sign-up");?></button>
        <div>/</div>
        <button class="reg-button" type="button"><?= t("nav-bar.sign-in");?></button>
      </div>
    </div>

<?php
  }
?>
