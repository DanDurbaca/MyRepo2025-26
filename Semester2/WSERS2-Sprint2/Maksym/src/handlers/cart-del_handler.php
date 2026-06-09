<?php
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $item = (int)($_POST["ItForDel"]);

    for ($i = 0; $i < count($_SESSION["Cart"]); $i++) {
      if ($_SESSION["Cart"][$i]["id"] === $item) {
        array_splice($_SESSION["Cart"], $i, 1);
        break;
      }
    }

    header("Location: ../../public/pages/cart/");
  }
