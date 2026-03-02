<?php
  function signup() {
?>

  <form id="sign-up" action="helpers/signup_action.php" method="post">
    <div class="close-button">X</div>

    <div>
      <label for="username"><?= t("signing.username");?></label><br>
      <input name="username" type="text" required><br>
    </div>

    <div>
      <label for="email"><?= t("signing.email");?></label><br>
      <input name="email" type="email" required><br>
    </div>

    <div>
      <label for="password"><?= t("signing.password");?></label><br>
      <input name="password" type="password" required><br>
    </div>

    <div>
      <label for="password-confirm"><?= t("signing.confirm");?></label><br>
      <input name="password-confirm" type="password" required><br>
    </div>

    <div>
      <label for="capcha"><?= t("signing.captcha", ["n1" => $_SESSION["captcha_num1"], "n2" => $_SESSION["captcha_num2"]]); ?></label><br>
      <input name="captcha" type="number" required>
    </div>

    <div>
      <input class="submit" type="submit" value="Sign up">
    </div>
  </form>

<?php
  }
?>
