<?php
require_once __DIR__ . "/../admin/includes/auth.php";
if (isLoggedIn()) {
  header("Location: " . appUrl("/user/welcome.php"));
} else {
  header("Location: " . publicUrl("/login.php"));
}
exit();
