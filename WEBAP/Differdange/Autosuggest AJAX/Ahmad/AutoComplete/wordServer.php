<?php
//connects to word database
$conn = new mysqli("localhost", "root", "", "wordsDB");
//when recived the requst sends out the suggested word
if (isset($_GET["giveSuggestedWord"])) {
    //build a list of suggested words and print them out:
    $sqlSelect = $conn->prepare("SELECT id,word FROM words WHERE word LIKE ? LIMIT 10");
    $sqlSelect->bind_param("s", $_GET["giveSuggestedWord"]);
    $sqlSelect->execute();
    $result = $sqlSelect->get_result();
    while ($row = $result->fetch_assoc()) {
        print("<div id='wordNu" . $row["id"] . "' >" . $row['word'] . "</div>");
    }
}
// onclick='rename("'wordNu" . $row["id"] . "'" . ")'