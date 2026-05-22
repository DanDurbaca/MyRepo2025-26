<?php
require_once __DIR__ . '/db.php';
require_login();
if (!is_admin()) { header('Location: welcome.php'); exit; }
$mysqli = db_connect();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $stmt = $mysqli->prepare("DELETE FROM env_record WHERE rec_id=?");
    $stmt->bind_param('i',$id); $stmt->execute();
}
header('Location: admin_measurements.php');
exit;
