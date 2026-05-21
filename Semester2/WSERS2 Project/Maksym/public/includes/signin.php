<?php
  function signin() {
    $errors = [];

    if (isset($_SESSION["signup_errors"])) {
      $errors = $_SESSION["signup_errors"];

      unset($_SESSION["signup_errors"]);
    }

    for ($i = 0; $i < count($errors); $i++) {
?>
      <div id="errors">
        <h2><?= $errors[$i] ?></h2>

        <div id="error-close">X</div>
      </div>
    <?php } ?>

    <form id="sign-in" action="../../helpers/signin_action.php" method="POST">
      <div class="close-button">X</div>

      <div>
        <label for="username"><?= t("signing.username");?></label><br>
        <input name="username" type="text" required><br>
      </div>

      <div>
        <label for="password"><?= t("signing.password");?></label><br>
        <input name="password" type="password" required><br>
      </div>

      <div>
        <label for="capcha"><?= t("signing.captcha", ["n1" => $_SESSION["captcha_num1"], "n2" => $_SESSION["captcha_num2"]]); ?></label><br>
        <input name="captcha" type="number" required>
      </div>

      <div>
        <input class="submit" type="submit" value="Sign in">
      </div>
    </form>

<?php
  }
?>
