<?php
session_start(); // Start the session to manage user data
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="mystyle.css">
    <?php
    include "queries.php";
    if($_SERVER["REQUEST_METHOD"]=="POST"){ // Check if the form is submitted
        $username = $_POST["username"];
        $email = $_POST["email"];
        $fName = $_POST["firstname"];
        $lName = $_POST["lastname"];
        $password = $_POST["password"];
        $confirmp = $_POST["confirm"];
        $formvalid = FALSE;
        if($username == ""){ // Validate username
            $errorun = " Username must be filled out";
        }
        $sql = "SELECT pk_userId FROM user WHERE username = '$username'";
        $result = mysqli_query($conn,$sql);
        if(mysqli_num_rows($result)>1){ // Check for unique username
            $errorun = " Username is not unique";
        }
        if($email == "" || !filter_var($email, FILTER_VALIDATE_EMAIL)){ // Validate email
            $errore = " Email must be filled out or it is not correct";
        }
        if($password === $confirmp){ // Validate password confirmation
            if($password == ""){ // Check for non-empty password
                $errorp = "Password must be filled out";
            }
        }
        else{ // Passwords do not match
            $errorp = "Passwords must be the same";
        }
        if(!isset($errore) && !isset($errorp) && !isset($errorun)){ // If no errors, form is valid
            $formvalid = TRUE;
        }
        if($formvalid == TRUE){
            $confirmReg = queriesUser($conn,$username,$password,$ful,$email,"insert"); // Create the account
        }
    }
    ?>
</head>
<body>
    <h1> Create your Account </h1>
    <form method = "POST">
        <p><label>Enter your username: </label><input type = "text" placeholder="new username" name = "username" value =""><?php if(isset($errorun)){print($errorun);}//output error?></p>
        <p><label>Enter your Full Name(optional): </label><input type = "text" placeholder="First Name" name = "firstname" value =""></p>
        <p><label>Enter your Full Name(optional): </label><input type = "text" placeholder="Last Name" name = "lastname" value =""></p>
        <p><label>Enter your email: </label><input type = "text" placeholder="abc@xyz.com" name = "email" value =""><?php if(isset($errore)){print($errore);}//output error?></p>
        <br></br>
        <label>Enter your password: </label><input type = "password" placeholder="password" name = "password" value ="">
        <p><label>Confirm your password: </label><input type = "password" placeholder="confirm" name = "confirm" value =""></p>
        <?php if(isset($errorp)){print($errorp);}?>
        <br><input type = "submit" name = "Create" value = "Create Account"></br>
        <?php if(isset($confirmReg)){print($confirmReg);}//output registration notification  ?>
    </form>
</body>
<footer>
    <?php include "footer.php"; ?>
</footer>
</html>