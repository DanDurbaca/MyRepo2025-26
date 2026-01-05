<?php
session_start();

$lang = $_POST["lang"] ?? "EN";

session_unset();
session_destroy();

header("Location: login.php?lang=" . $lang);
exit;
?>
