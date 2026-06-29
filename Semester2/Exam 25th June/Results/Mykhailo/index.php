<?php 
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php if (!isset($_SESSION['Countries '])): ?>
        <?php 
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error']) . '</p>';
            unset($_SESSION['error']);
        }
        ?>
    <form action="countries.php" method ="POST">
        <select name ="Countries" onchange = "this.form.submit">
     <label for="Countries ">
       
     <label>
       <input type="list" id ="list" name="list" require >
        <option value ="<?php if(isset($_POST(("SELECT * FROM Countries  WHERE Name = ?");)))?>">></option>
       
    </input>
    </form>
    <?php else: ?>
        <?php if (!isset($_SESSION['Cities '])): ?>
        <?php 
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error']) . '</p>';
            unset($_SESSION['error']);
        }
        ?>

        <form action="cities.php" method="POST">
            <label for="cities "></label>
            <input type="list" id="cities" name="cities" required>
             <input type="optipon" name="cititId" value="<?= intval($cities['cityId']) ?>">
    </input> 
            
        </form>

        <br>

       

    <?php endif; ?>
    <form action="people.php" method="POST">
   


    </form>
</body>
</html>