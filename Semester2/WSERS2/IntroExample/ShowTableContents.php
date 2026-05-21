<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <form>
        <input type="number" placeholder="minimum calories" name="calories">
        <input type="submit" value="Filter">
    </form>

    <?php
    // create a connection to the db: STEP 1
    $connection = new mysqli("localhost", "root", "", "FirstDb2026"); // option using new
    //$connection = mysqli_connect("localhost", "root", "", "FirstDb2026"); // SAME THING !!

    // create (prepare) an sql query: STEP 2
    if (isset($_GET["calories"])) {
        // make another select with a WHERE clause:
        $sqlQuery = $connection->prepare("SELECT * from Ingredients where ingredientCalories>=?");
        $sqlQuery->bind_param("i", $_GET["calories"]);
    } else {
        $sqlQuery = $connection->prepare("SELECT * from Ingredients");
        // STEP 3 (optional) : bind additional parameters to our sql statement
        // we will SKIP for the moment this step ... we do not have any parameters for the sql statement  
    }



    // step 4: EXECUTE The sql statement
    $sqlQuery->execute(); // not difficult 

    // step 5: get the result
    $result = $sqlQuery->get_result();

    ?>
    <table>
        <tr>
            <th>Ingredient name</th>
            <th>Ingredient calorories</th>
        </tr>

        <?php
        // step 6: Display the results in a LOOP:
        while ($row = $result->fetch_assoc()) {
            // $row is an associative array that has keys - the column names and values the current selected row values 
        ?>
            <tr>
                <td><?= $row["ingredientName"] ?></td>
                <td><?= $row["ingredientCalories"] ?></td>
            </tr>
        <?php

        }
        ?>
    </table>

</body>

</html>