<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location:login.php");
}
else{
    $usrname = $_SESSION['username'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="mystyle.css">
    <?php
        include "navbar.php";
        include "queries.php";
    ?>
</head>
<body>
    <h1>Greetings <?php print($usrname)?>. This is your user management page</h1>
    <h2><a href="usr_page.php?changeData=1">Change information</a></h2>
    <?php
        if(isset($_GET["changeData"])){
            print("<form method='POST'>");?>
            <p>New Username:</p><p><input type = 'text' name = 'newUser' value = '<?php $sql = $conn -> prepare("SELECT pk_username FROM user WHERE pk_username = '$usrname'");
            $sql->execute();
            $result=$sql->get_result();
            while($row=$result->fetch_assoc()){
            print($row["pk_username"]);} //current username
            ?>'></p>
            <p>New Password:</p><p><input type = 'password' name = 'passwd' value = '<?php $sql = $conn -> prepare("SELECT password FROM user WHERE pk_username = '$usrname'");
            $sql->execute();
            $result=$sql->get_result();
            while($row=$result->fetch_assoc()){
            print($row["password"]);} //current password
            ?>'></p>
            <p>New First Name:</p><p><input type = 'text' name = 'newFname' value = '<?php $sql = $conn -> prepare("SELECT firstName FROM user WHERE pk_username = '$usrname'");
            $sql->execute();
            $result=$sql->get_result();
            while($row=$result->fetch_assoc()){ //current first name
            print($row["firstName"]);}?>'></p>
            <p>New Last Name:</p><p><input type = 'text' name = 'newLname' value = '<?php $sql = $conn -> prepare("SELECT lastName FROM user WHERE pk_username = '$usrname'");
            $sql->execute();
            $result=$sql->get_result();
            while($row=$result->fetch_assoc()){ //current last name
            print($row["lastName"]);}?>'></p>
            <p>New Email:</p><p><input type = 'text' name = 'newEmail' value = '<?php $sql = $conn -> prepare("SELECT email FROM user WHERE pk_username = '$usrname'");
            $sql->execute();
            $result=$sql->get_result();
            while($row=$result->fetch_assoc()){ //current email
            print($row["email"]);}?>'><?php if(isset($errore)){print($errore);}?></p>
            <p><input type = 'submit' name = 'updateAccount' value = "Update My Account">
        <?php
            print("</form>");
            if($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST["updateAccount"])){ //handle form submission
                $newUsr = $_POST["newUser"];
                $newPass = $_POST["passwd"];
                $newFname = $_POST["newFname"];
                $newLname = $_POST["newLname"];
                $newEmail = $_POST["newEmail"];
                if(!filter_var($newEmail, FILTER_VALIDATE_EMAIL)){ //validate email
                    $errore = " Your email has to have @service.xyz";
                }
                if($newUsr == "" && $newPass == ""){ //validate username and password
                    $errorN = "User Name and Password fields must be filled out";
                }
                if(!isset($errore) && !isset($errorN)){ //all validations passed
                    $valid = TRUE;
                }
                if($valid == TRUE){ //check for unique username
                    $sql = $conn -> prepare("SELECT pk_username FROM user");
                    $sql -> execute();
                    $result = $sql -> get_result();
                    while($row=$result->fetch_assoc()){
                        if($newUsr==$row["pk_username"]){ //check for username uniqueness
                            $errorUnique = "Your new username is already taken, try another one";
                        }
                    }

                    if(!isset($errorUnique)){ //proceed with update
                       $res = queriesUserManagement($conn,$newUsr,$newPass,$newEmail,$newFname,$newLname,$usrname); //update query
                       $_SESSION['username'] = $newUsr;
                       header("Location:usr_page.php");
                       print($res);
                    }
                }
            }
        }
        ?>
        <h3>Add friends here:</h3>
        <form method = "POST"> <!-- Friend request form -->
            <input type = "text" name = "friendUserName" placeholder = "Name of a friend">
            <input type = "Submit" name = "sendFriendRequest" value = "Send Friend Request">
        </form>
        <?php
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['sendFriendRequest'])){ //handle friend request
            $receivername = $_POST['friendUserName']; // user you want to add
            $sendername = $_SESSION['username'];
            $sql = $conn -> prepare("SELECT * FROM isfriend WHERE (pkfk_user_user = ? AND pkfk_user_friend = ?) OR (pkfk_user_friend = ? AND pkfk_user_user = ?);");
            $sql -> bind_param("ssss",$sendername,$receivername,$sendername,$receivername); //check existing requests or friendship
            $sql -> execute();
            $result = $sql -> get_result();
            if(mysqli_num_rows($result) > 0){
                print("Request is pending or you are already friends");
            }
            else{
                $sql = $conn -> prepare("INSERT INTO isfriend(pkfk_user_user,pkfk_user_friend,status) VALUES(?,?,'pending');");
                $sql -> bind_param("ss",$sendername,$receivername); //insert new friend request
                if($sql -> execute()){
                    print("Friend Request has been successfully sent");
                }
                else{
                    mysqli_error($conn);
                }
            }
        }
        ?>
        <h3>Here are your friend requests: </h3>
        <?php
        $uid = $_SESSION['username'];
        $sql = $conn -> prepare("SELECT * FROM isfriend WHERE pkfk_user_friend = ? AND status = 'pending' ;");
        $sql -> bind_param("s",$uid); //get friend requests
        $sql -> execute();
        $result = $sql -> get_result();
        if(mysqli_num_rows($result) > 0){ //output requests
            while($row = $result -> fetch_assoc()){
                print("<p>Request from ".$row["pkfk_user_user"]." <a href='usr_page.php?action=addFriend&id=".$row["pkfk_user_user"]."'>Add Friend</a> <a href='usr_page.php?action=decline&id=".$row["pkfk_user_user"]."'>Decline</a></p>");
            }
        }
        else{
            print("No friend requests yet"); //no requests
        }
        if(isset($_GET["action"])){ //handle friend request actions
            $action = $_GET["action"];
            $adderId = $_GET["id"];
            if($action === "addFriend"){
                $sql = $conn -> prepare("UPDATE isfriend SET status = 'accepted' WHERE pkfk_user_friend = ? AND pkfk_user_user = ?;"); //accept friend request
                $sql -> bind_param("ss",$uid,$adderId);
                if($sql -> execute()){
                    print("friend has been added");
                    header("Location:usr_page.php");
                }
                else{
                    mysqli_error();
                }
            }
            if($action === "decline"){
                $sql = $conn -> prepare("DELETE FROM isfriend WHERE pkfk_user_friend = ? AND pkfk_user_user = ?;"); //decline friend request
                $sql -> bind_param("ss",$uid,$adderId);
                if($sql -> execute()){
                    print("friend request has been declined");
                    header("Location:usr_page.php");
                }
                else{
                    mysqli_error();
                }
            }
        }
        print("<h3>Here are your friends, $uid</h3>"); //output friends list
        $uid = $_SESSION['username']; 
        $sql = $conn -> prepare("SELECT * FROM isfriend WHERE (pkfk_user_friend = ? OR pkfk_user_user = ?) AND status = 'accepted' ;");
        $sql -> bind_param("ss",$uid,$uid);
        $sql -> execute();
        $result = $sql -> get_result();
        while($row = $result -> fetch_assoc()){
                print("<p>".$row["pkfk_user_user"]."<a href='usr_page.php?action=removeFriend&id=".$row["pkfk_user_user"]."'>Remove Friend</a></p>");
        }
        if(isset($_GET["action"])){ //handle removing friends
            $action = $_GET["action"];
            $removerId = $_GET["id"];
            if($action === "removeFriend"){
                $sql = $conn -> prepare("DELETE FROM isfriend WHERE (pkfk_user_friend = ? AND pkfk_user_user = ?) OR (pkfk_user_user = ? AND pkfk_user_friend = ?);");
                $sql -> bind_param("ssss",$uid,$removerId,$uid,$removerId); //remove friend from list
                if($sql -> execute()){
                    print("friend has been removed");
                    header("Location:usr_page.php");
                }
                else{
                    mysqli_error();
                }
            }
        }
        ?>
</body>
<footer>
    <?php include "footer.php"; ?>
</footer>
</html>