<?php
include 'CommonCode.php';
requireLogin();
header('Content-Type: application/json');

$username = $_SESSION['username'];
$serial   = isset($_GET['station']) ? trim($_GET['station']) : '';
$start    = isset($_GET['start'])   && $_GET['start'] !== '' ? $_GET['start'] : '';
$end      = isset($_GET['end'])     && $_GET['end']   !== '' ? $_GET['end']   : '';

if ($serial === '') { echo json_encode([]); exit; }

$st = getStationBySerial($serial);
if (!$st || $st['fk_user_owns'] !== $username) { echo json_encode([]); exit; }

$startSql = $start !== '' ? str_replace('T', ' ', $start) . ':00' : '';
$endSql   = $end   !== '' ? str_replace('T', ' ', $end)   . ':00' : '';

echo json_encode(fetchMeasurementsForStation($serial, $startSql, $endSql));
