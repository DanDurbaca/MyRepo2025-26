<?php
session_start();
if (!isset($_SESSION["userLogged"])) {
    $_SESSION["userLogged"] = false;
}

if (!isset($_SESSION["canAddProducts"]) && isset($_SESSION["Llogin"])) {
    $fhandler = fopen("clients.csv", "r");
    while (!feof($fhandler)) {
        $line = fgets($fhandler);
        if ($line === false)
            break;

        $cols = explode(";", $line);

        if ($_SESSION["Llogin"] === trim($cols[0]) && trim($cols[4]) === "admin") {
            $_SESSION["canAddProducts"] = true;
            return;
        }
    }
    $_SESSION["canAddProducts"] = false;
    fclose($fhandler);
}
function userExists($username)
{
    if (!file_exists("clients.csv") || filesize("clients.csv") <= 0)
        return false;

    $fhandler = fopen("clients.csv", "r");
    fgets($fhandler);

    while (!feof($fhandler)) {
        $line = fgets($fhandler);
        if ($line === false)
            break;

        $cols = explode(";", $line);

        if ($cols[0] === $username) {
            fclose($fhandler);
            return true;
        }
    }
    fclose($fhandler);
    return false;
}

function findUserByLoginAndPassword($login, $password)
{
    if (!file_exists("clients.csv") || filesize("clients.csv") <= 0)
        return false;

    $fhandler = fopen("clients.csv", "r");
    fgets($fhandler);

    while (!feof($fhandler)) {
        $line = fgets($fhandler);
        if ($line === false)
            break;

        $line = trim($line);
        $cols = explode(";", $line);

        if (($cols[0] === $login || $cols[2] === $login) && password_verify($password, $cols[3])) {
            fclose($fhandler);
            return true;
        }
    }
    fclose($fhandler);
    return false;
}