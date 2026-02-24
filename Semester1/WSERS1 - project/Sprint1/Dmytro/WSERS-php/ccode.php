<?php
function userExists($username)
{
    if (!file_exists("clients.csv") || filesize("clients.csv") <= 0)
        return false;

    $fhandler = fopen("clients.csv", "r");
    fgets($fhandler);

    while (!feof($fhandler)) {
        $line = fgets($fhandler);
        if ($line === false) break;

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
        if ($line === false) break;
        
        $line = trim($line);
        $cols = explode(";", $line);

        if (($cols[0] === $login || $cols[2] === $login) && $cols[3] === $password) {
            fclose($fhandler);
            return true;
        }
    }
    fclose($fhandler);
    return false;
}