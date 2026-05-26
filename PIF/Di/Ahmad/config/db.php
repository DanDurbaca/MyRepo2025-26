<?php
$conn = new mysqli("localhost", "root", "", "portableindoorfeedback");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
