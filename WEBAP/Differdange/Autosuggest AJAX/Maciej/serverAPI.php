<?php
// create connection to the database
$conn = new mysqli("localhost", "root", "", "DictionaryDB");

//create a list of suggestions based on what the user is typing
if(isset($_GET["giveListOfSuggestions"])) {
    // build a list of suggestions and print it out
    $sqlSelect = $conn->prepare(
        "SELECT * 
        FROM words
        WHERE wordName LIKE ?
        ORDER BY wordName ASC"
    );
    //store constant value that is typed by the user with a % at the end
    $modifiedValue = $_GET["giveListOfSuggestions"] . "%";
    // bind the parameter as a string and execute the query
    $sqlSelect->bind_param("s", $modifiedValue);
    $sqlSelect->execute();
    //get the result set from the executed query
    $result = $sqlSelect->get_result();
    // fetch each row from the result set
    while($row = $result->fetch_assoc()) {
        print('<option value="' . $row["wordName"] . '"></option>');
    }
}