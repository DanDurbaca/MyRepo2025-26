<?php
$mysqli = new mysqli("localhost", "root", "", "sofjo685");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$q = $_GET['q'];
$q = $mysqli->real_escape_string($q);

$sql = "SELECT word FROM dictionary WHERE word LIKE '$q%' LIMIT 10";
$result = $mysqli->query($sql);

while($row = $result->fetch_assoc()) {
    echo "<div>" . $row['word'] . "</div>";
}

$mysqli->close();
?>
