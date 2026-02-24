<?php
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json");
ob_clean();

if (!isset($_FILES["file"])) {
    echo json_encode(["error" => "No file received"]);
    exit();
}

$file = $_FILES["file"];

if ($file["error"] !== UPLOAD_ERR_OK) {
    echo json_encode(["error" => "Upload error"]);
    exit();
}

$targetDir = __DIR__ . "/Pictures/";

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$filename = time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "", $file["name"]);
$target = $targetDir . $filename;

if (move_uploaded_file($file["tmp_name"], $target)) {
    echo json_encode(["filename" => $filename]);
} else {
    echo json_encode(["error" => "Failed to move file"]);
}
?>
