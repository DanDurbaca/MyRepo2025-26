<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
    $connection = new mysqli("localhost", "root", "", "Ppl");
    /*if (isset($_POST["value"])){
    
    $sqlFindCities=$connection->prepare("SELECT * FROM Cities WHERE CountryId = ?");
    $sqlFindCities->bind_param("i", $_POST["value"] ); 
    $sqlFindCities->execute();
    $sqlResult = $sqlFindCities -> get_result();
    }*/
    ?>
    <form >
        <select name="country" onchange="this.form.submit()">
            <option value="0" name="Please select">Please select</option>
            <option value="1" name="France">France</option>
            <option value="2" name="Germany">Germany</option>       
            <option value="3" name="Romania">Romania</option>    
            <option value="4" name="Italy">Italy</option> 
            <option value="5" name="Spain">Spain</option> 
            <option value="6">Luxembourg</option> 
            <option value="7">Poland</option>
            <option value="8">Netherlands</option>
            <option value="9">Belgium</option>
            <option value="10">Portugal</option>
        </select>
    </form>

    

<main>
    <?php
		$sqlQuery= $connection -> prepare("select * from Ppl");
		$sqlQuery->execute();
		$result=$sqlQuery->get_result();
		while ($row=$result->fetch_assoc()) {
		?>
			<figure>
				<figcaption><?php $row["PersonName"]?></figcaption>
                <figcaption><?php $row["Age"]?></figcaption>
			</figure>
            <?php
		}
		?>
</main>
</body>
</html>