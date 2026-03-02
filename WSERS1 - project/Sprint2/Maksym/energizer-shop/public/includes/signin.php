<?php
  function signin() {
?>

    <form id="sign-in" action="">
      <div class="close-button">X</div>

      <div>
        <label for="username"><?= t("signing.username");?></label><br>
        <input id="username" type="text" required><br>
      </div>

      <div>
        <label for="password"><?= t("signing.password");?></label><br>
        <input id="password" type="password" required><br>
      </div>

      <div>
        <label for="capcha"><?= t("signing.captcha", ["n1" => $_SESSION["captcha_num1"], "n2" => $_SESSION["captcha_num2"]]); ?></label><br>
        <input id="captcha" type="number" required>
      </div>

      <div>
        <input class="submit" type="submit" value="Sign up">
      </div>
    </form>

<?php
  }
?>
