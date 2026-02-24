<?php
  $_SESSION["captcha_num1"] = rand(1, 99);
  $_SESSION["captcha_num2"] = rand(1, 99);

  $_SESSION["captcha_answer"] = $_SESSION["captcha_num1"] + $_SESSION["captcha_num2"];
