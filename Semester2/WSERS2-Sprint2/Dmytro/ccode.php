<?php
session_start();
$imgTegArr = ["<img src='pics/shopingCart.png' alt='shoping cart' width=25px>"];
$myMentorshipShopDB = new mysqli("127.0.0.1", "root", "", "mentorshipshop");

$_SESSION["userLogged"] ??= false;

if (!isset($_SESSION["canAddProducts"]) && isset($_SESSION["email"])) {
    $_SESSION["canAddProducts"] = $_SESSION["userType"] === "admin" ? true : false;
}

function validate_product_and_add($productNameEN, $productNameUA, $price, $productDeskEN, $productDeskUA)
{
    global $tArray;
    global $myMentorshipShopDB;
    $productNameEN = trim($productNameEN);
    $productNameUA = trim($productNameUA);
    $price = trim($price);
    $productDeskEN = trim($productDeskEN);
    $productDeskUA = trim($productDeskUA);

    if ($productNameEN === '' || $productNameUA === '' || $price === '' || $productDeskEN === '' || $productDeskUA === '') {
        echo "<div class='alert alert-error'>" . $tArray["EmptyIn"] . "</div>";
        return;
    }
    if (!preg_match('/^(\d+(?:\.\d{2}))\$\s.+$/', $price, $matches)) {
        echo "<div class='alert alert-error'>" . $tArray["addPInvalidPrice"] . "</div>";
        return;
    }
    $amount = (float) $matches[1];

    if ($amount > 1_000_000 || $amount <= 0) {
        echo "<div class='alert alert-error'>" . $tArray["addPTooHighPrice"] . "</div>";
        return;
    }

    $clients = $myMentorshipShopDB->prepare("INSERT INTO products (productNameEN, productNameUA, price, descriptionEN, descriptionUA) VALUES (?, ?, ?, ?, ?);");
    $clients->bind_param("sssss", $productNameEN, $productNameUA, $price, $productDeskEN, $productDeskUA);
    $clients->execute();
    echo "<div class='alert alert-success'>" . $tArray["addPSuccess"] . "</div>";
}
function validate_registration_and_add($username, $email, $pass, $passc)
{
    global $tArray;
    global $myMentorshipShopDB;
    $username = trim($username);
    $email = trim($email);

    if ($username === '' || $email === '' || $pass === '' || $passc === '') {
        echo "<div class='alert alert-error'>" . $tArray["EmptyIn"] . "</div>";
        return;
    }

    if ($pass !== $passc) {
        echo "<div class='alert alert-error'>" . $tArray["RegPassNotConf"] . "</div>";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<div class='alert alert-error'>" . $tArray["RegInvalidEmail"] . "</div>";
    } else {
        $userType = 'user';
        $password = password_hash($pass, PASSWORD_DEFAULT);
        try {
            $clients = $myMentorshipShopDB->prepare("INSERT INTO clients (username, email, pass, userType) VALUES (?, ?, ?, ?);");
            $clients->bind_param("ssss", $username, $email, $password, $userType);
            $clients->execute();
            echo "<div class='alert alert-success'>" . $tArray["RegSuccess"] . "</div>";
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                echo "<div class='alert alert-error'>" . $tArray["RegTaken"] . "</div>";
            }
        }
    }
}

function validate_user_login($email, $password)
{
    global $tArray;
    global $myMentorshipShopDB;
    global $lang;
    $email = trim($email);

    if ($password === '' || $email === '') {
        echo "<div class='alert alert-error'>" . $tArray["EmptyIn"] . "</div>";
        return;
    }
    $client = $myMentorshipShopDB->prepare("SELECT pass, userType FROM clients WHERE email = ?;");
    $client->bind_param("s", $email);
    $client->execute();

    $result = $client->get_result();
    $row = $result->fetch_assoc();

    if ($result->num_rows === 0 || !password_verify($password, $row['pass'])) {
        echo "<div class='alert alert-error'>" . $tArray["LoginInvalid"] . "</div>";
        return;
    }
    echo "<div class='alert alert-success'>" . $tArray["LoginSuccess"] . "</div>";

    $_SESSION["userLogged"] = true;
    $_SESSION['email'] = $email;
    $_SESSION['userType'] = $row['userType'];

    header("Location: welcome.php?lang=" . $lang);
    exit;
}