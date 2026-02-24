<?php

$connection = new mysqli("localhost", "root", "", "testAjax");

if (isset($_GET["cityName"])) {
    $selectPpl = $connection->prepare("Select * from Persons where City=?");
    $selectPpl->bind_param("s", $_GET["cityName"]);
    $selectPpl->execute();
    $results = $selectPpl->get_result();
    while ($row = $results->fetch_assoc()) {
        print ($row["LastName"] . " " . $row["FirstName"]) . "<br>";
    }
} else {
    print("Please provide a filter city for people");
}
