<?php
require_once __DIR__ . "/../admin/includes/CommonCode.php";
session_unset();
session_destroy();
header("Location: " . publicUrl("/login.php"));
exit();
?> 
