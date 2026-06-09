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
   //Create connection
   $connection = new mysqli("localhost", "root","","firstdb2026ad");
   //create an sql query
   if (isset($_GET["calories"])){
       $sqlQuery = $connection->prepare("SELECT * from Ingridients where ingridientCalories>=?;");
       $sqlQuery->bind_param("i", $_GET["calories"]);
    }
    else{
        $sqlQuery = $connection->prepare("SELECT * FROM Ingridients;");
    }
    //bind additional parameters to our sql statement
 
    //execute the sql statement
    $sqlQuery->execute();
    //get the result
    $result = $sqlQuery->get_result();
    ?>
    <table>
        <tr>
            <th>Ingredient name</th>
            <th>Ingredient calories</th>
        </tr>
    <?php
    //display the result in a loop
    while($row=$result->fetch_assoc()){
    ?>
        <tr>
            <td><?= $row["ingridientName"]?></td>
            <td><?= $row["ingridientCalories"]?></td>
        </tr>
    <?php
    }
    ?>
    </table>
</body>
</html>