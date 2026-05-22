<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="mystyle.css">
    <?php
    session_start();
    if(isset($_GET["logout"]) || isset($_GET["login"])){ // Handle logout or login redirection and empty session
        session_unset();
        session_destroy();
        session_start();
    }
    include "queries.php";
    if($_SERVER["REQUEST_METHOD"]=="POST"){
        $usrname = $_POST["username"];
        $password = $_POST["password"];
        if($username != "" && $password != ""){
            $sql = "SELECT pk_username,role from user WHERE pk_username = '$usrname' AND password = '$password'";
            $result = mysqli_query($conn,$sql);
            if(mysqli_num_rows($result) > 0){
                while($row = mysqli_fetch_assoc($result)){
                    $role = $row["role"]; // Get user role
                }
                $_SESSION["username"] = $usrname; //store username in session
                $_SESSION["role"] = $role; //store role in session
                header("Location: home_page.php"); // Redirect to home page
            }
            else{
                $error = "Wrong username or password"; // Invalid credentials
            }
            
        }
        else{
            $error = "Wrong username or password"; // Empty fields
        }
    }
    ?>
</head>
<body>
    <form method = "POST">
        <label>Enter your username:</label><br></br><input type = "text" name = "username" placeholder = "username" value = "">
        <br></br>
        <label>Enter your password:</label><br></br><input type = "password" name = "password" placeholder = "password" value = "">
        <?php if(isset($error)){print("<p>$error</p>");} //error output  ?>
        <br>No account? Create it <a href="create_account.php">here</a></br>
        <br><input type = "Submit" name = "Log-In" value = "Log-In"></br>
    </form>    
</body>
<footer>
    <?php include "footer.php"; ?>
</footer>
</html>