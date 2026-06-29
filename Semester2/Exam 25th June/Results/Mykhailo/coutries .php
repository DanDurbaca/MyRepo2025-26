<?php
session_start();

// Database configuration settings
$host = 'localhost';
$user = 'root'; 
$pass = '';     
$db   = 'Ppl';

// Establish connection to MySQL
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}


if (isset($_POST['Countries'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}


if (isset($_POST['Countries '])) {
    $countriess = trim($_POST['CountryId']);

    if (empty( $countriess)) {
        $_SESSION['error'] = "Country must be selected ";
        header("Location: index.php");
        exit();
    }

    // Step 1: Query the  Countries 
    $stmt = $conn->prepare("SELECT * FROM Countries  WHERE Name = ?");
    $stmt->bind_param("s", $countriess);
    $stmt->execute();
    $result = $stmt->get_result();

    $stmt->close();
    
    // Redirect back to main dashboard layout
    header("Location: index.php");
    exit();
}


header("Location: index.php");
exit();
?>
<?php 
$db = getPpl();
    $stmt = $db->prepare("SELECT * FROM Countries ");
    if ($stmt) {
        $stmt->execute();
        $res = $stmt->get_result();
        while ($product = $res->fetch_assoc()) {
        } 
    }

?>