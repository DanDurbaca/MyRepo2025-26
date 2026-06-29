<?php
session_start();

$host = 'localhost';
$db   = 'Exam2026';
$user = 'root';
$pass = '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>WSERS EXAM</title>
</head>

<body>
    <div class="box">

        <form method="get" style="display:inline-block; margin-left:20px;">
            <select name="lang" onchange="this.form.submit()">
                <option>Please select a country</option>
            </select>
        </form>

        <form method="get" style="display:inline-block; margin-left:20px;">
            <select name="lang" onchange="this.form.submit()">
                <option>Name</option>
            </select>
        </form>

        <form method="get" style="display:inline-block; margin-left:20px;">
            <select name="lang" onchange="this.form.submit()"> 
                <option>Age</option>
            </select>
        </form>
</body>
</div>

</html>

