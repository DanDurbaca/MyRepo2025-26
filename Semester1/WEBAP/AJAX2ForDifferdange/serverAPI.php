<?php

// create connection to the db:
$connection = new mysqli("localhost", "root", "", "citiesandcountries");

// we need to create a list of countries ONLY when we are asked for it:

if (isset($_GET["giveListOfCountries"])) {
    // build a list of countries and print them out:
    $sqlSelect = $connection->prepare("SELECT * from countries");
    $sqlSelect->execute();
    $result = $sqlSelect->get_result();
    while ($row = $result->fetch_assoc()) {
        print("<option value=" . $row["CountryID"] . ">" . $row["NameOfCountry"] . "</option>");
    }
}

if (isset($_GET["giveListOfCitiesBelongingTo"])) {
    $sqlSelect = $connection->prepare("SELECT * from cities where CountryID=?");
    $sqlSelect->bind_param("i", $_GET["giveListOfCitiesBelongingTo"]);
    $sqlSelect->execute();
    $result = $sqlSelect->get_result();
    while ($row = $result->fetch_assoc()) {
        print($row["NameOfCity"] . "<br>");
    }
}
