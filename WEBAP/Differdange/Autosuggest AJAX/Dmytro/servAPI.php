<?php
$connection = new mysqli("localhost", "root", "", "dictionary");

if (isset($_GET["findWords"])) {
    $wordLookup = $_GET["findWords"] . "%"; //Preparing a variable for LIKE "input%" statement of the SQL because "?%" does not work, nor ?%
    $sql = $connection->prepare("SELECT * FROM words WHERE word LIKE ?");
    $sql->bind_param("s", $wordLookup); //binding a wordLookup variable resulting in SQL script <<SELECT word FROM words WHERE word LIKE "userInput%">>
    $sql->execute();
    $result = $sql->get_result();
    if (mysqli_num_rows($result) > 100) {
        print("Too many words, continue writing");
    } elseif (mysqli_num_rows($result) == 0) {
        print("No word found");
    } else {
        while ($row = $result->fetch_assoc()) {
            print("<div id = " . $row["pk_wordId"] . " onClick=callFunc(" . $row["pk_wordId"] . ")" . ">" . $row["word"] . "</div>");
        }
    }
}
