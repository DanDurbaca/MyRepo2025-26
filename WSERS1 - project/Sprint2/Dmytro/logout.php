<?php
include_once "ccode.php";
include_once "navbar.php";

$_SESSION = [];
session_destroy();

header("Location: welcome.php?lang=" . $lang);