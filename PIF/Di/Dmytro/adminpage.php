<?php
    /**
     * Admin Page - User and Station Management
     * 
     * This page allows administrators to manage users, stations, and view measurements.
     * Only accessible to users with "Admin" role. Includes functionality to create, update, and delete users and stations.
        */
    session_start();
    include "queries.php";
    if(!isset($_SESSION["username"])){ //check for login
        header("Location:login.php");
    }
    else{
        $username = $_SESSION["username"];
    }
    if($_SESSION["role"]!="Admin"){ //check for admin role
        header("Location:home_page.php");
    }

    if($_SERVER["REQUEST_METHOD"] == "POST"){ //handle form submissions
            if(isset($_POST["updateUser"])){ //edit users
                $uname = $_POST["uName"];
                $oldname = $_GET["id"];
                $pwd = $_POST["password"];
                $fName = $_POST["firstName"];
                $lName = $_POST["lastName"];
                $email = $_POST["email"];
                if(isset($_POST["admin"])){
                    $role = "Admin";
                }
                else{
                    $role = "User";
                }
                $sql = $conn -> prepare("UPDATE user SET password = ?, firstName = ?, lastName = ?, email = ?, role = ?, pk_username = ? WHERE pk_username = ?;");
                $sql -> bind_param("sssssss",$pwd,$fName,$lName,$email,$role,$uname,$oldname);
                if($sql -> execute()){
                    header("Location: adminpage.php");
                    exit();
                }
                else{
                    mysqli_error($conn);
                }
            }
            if(isset($_POST["newUser"])){ //create new user
                $uname = $_POST["uName"];
                $pwd = $_POST["password"];
                $fName = $_POST["firstName"];
                $lName = $_POST["lastName"];
                $email = $_POST["email"];
                if(isset($_POST["admin"])){
                    $role = "Admin";
                }
                else{
                    $role = "User";
                }
                $sql = $conn -> prepare("INSERT INTO user(pk_username,password,firstName,lastName,email,role) VALUES(?,?,?,?,?,?);");
                $sql -> bind_param("ssssss",$uname,$pwd,$fName,$lName,$email,$role);
                if($sql -> execute()){
                    header("Location:adminpage.php");
                    exit();
                }
                else{
                    mysqli_error($conn);
                }
            }
            if(isset($_POST["updateStation"])){ //edit station
                $snum = $_POST["serialNum"];
                $sname = $_POST["statName"];
                $sdesc = $_POST["statDesc"];
                $sql = $conn -> prepare("UPDATE station SET name = ?, description = ? WHERE pk_serialNumber = ?;");
                $sql -> bind_param("sss",$sname,$sdesc,$snum);
                if($sql -> execute()){
                    header("Location:adminpage.php");
                    exit();
                }
                else{
                    mysqli_error($conn);
                }
            }
            if(isset($_POST["newStation"])){ //create new station
                $snum = $_POST["serialNum"];
                $sname = $_POST["statName"];
                $sdesc = $_POST["statDesc"];
                $sql = $conn -> prepare("INSERT INTO station(pk_serialNumber,name,description) VALUES(?,?,?);");
                $sql -> bind_param("sss",$snum,$sname,$sdesc);
                if($sql -> execute()){
                    header("Location:adminpage.php");
                    exit();
                }
                else{
                    mysqli_error($conn);
                }
            }
            if(isset($_POST["editCol"])){ //edit collection
                $colName = $_POST["colName"];
                $colDesc = $_POST["colDesc"];
                $colId = $_GET["id"];
                $sql = $conn -> prepare("UPDATE collection SET name = ?, description = ? WHERE pk_collection = ?;");
                $sql -> bind_param("sss",$colName,$colDesc,$colId);
                if($sql -> execute()){
                    header("Location:adminpage.php");
                    exit();
                }
                else{
                    mysqli_error($conn);
                }
            }
            if(isset($_POST["createCol"])){ //create new collection
                $colName = $_POST["colName"];
                $colDesc = $_POST["colDesc"];
                $sql = $conn -> prepare("INSERT INTO collection(name,description,fk_user_creates) VALUES(?,?,?);");
                $sql -> bind_param("sss",$colName,$colDesc,$username);
                if($sql -> execute()){
                    header("Location:adminpage.php");
                    exit();
                }
                else{
                    mysqli_error($conn);
                }
            }
        }
        if(isset($_GET["action"])){ //handle delete actions
            $action = $_GET["action"];
            if($action === "userDelete"){ //delete user
                $id = $_GET["id"];
                $sql = $conn -> prepare("DELETE FROM user WHERE pk_username = ?;");
                $sql -> bind_param("s",$id);
                if($sql -> execute()){
                    header("Location:adminpage.php");
                    exit();
                }
                else{
                    mysqli_error($conn);
                }
            }
            if($action === "deleteStation"){ //delete station
                $id = $_GET["id"];
                $sql = $conn -> prepare("DELETE FROM station WHERE pk_serialNumber = ?;");
                $sql -> bind_param("s",$id);
                if($sql -> execute()){
                    header("Location:adminpage.php");
                    exit();
                }
                else{
                    mysqli_error($conn);
                }
            }
            if($action === "deleteRecord"){ //delete measurement record
                $id = $_GET["id"];
                $sql = $conn -> prepare("DELETE FROM measurement WHERE pk_measurement = ?;");
                $sql -> bind_param("s",$id);
                if($sql -> execute()){
                    header("Location:adminpage.php");
                    exit();
                }
                else{
                    mysqli_error($conn);
                }
            }
            if($action === "deleteCol"){ //delete collection
                $id = $_GET["id"];
                $sql = $conn -> prepare("DELETE FROM collection WHERE pk_collection = ?;");
                $sql -> bind_param("s",$id);
                if($sql -> execute()){
                    header("Location:adminpage.php");
                    exit();
                }
                else{
                    mysqli_error($conn);
                }
            }
        }
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="mystyle.css">
</head>
<body>
    <?php include "navbar.php"; ?>
<div class="container">
    <?php
        $query = "SELECT *
                FROM user
                ";
        $sql = $conn -> prepare($query);
        $sql -> execute();
        $result = $sql->get_result();
                if(mysqli_num_rows($result)>0){ //display users
                    print("<table>");
                    print("<tr><th>Username</th><th>First name</th><th>Last name</th><th>Password</th><th>Email</th><th>Role</th></tr>");
                    while($row = mysqli_fetch_assoc($result)){
                        print("<tr><td>".$row["pk_username"]."</td><td>".$row["firstName"]."</td><td>".$row["lastName"]."</td><td>".$row["password"]."</td><td>".$row["email"]."</td><td>".$row["role"]."</td><td><a href='adminpage.php?action=userEdit&id=".$row["pk_username"]."'>Edit User</a></td><td><a href='adminpage.php?action=userDelete&id=".$row["pk_username"]."'>Delete User</a></td></tr>");
                    }
                    print("</table>");
                    print("<br>");
                }

    ?>
    <form method = "POST">
        <input type="text" name="uName" placeholder="User Name" value="<?php if(isset($_GET["action"]) && $_GET["action"] === "userEdit"){$sql = $conn -> prepare("SELECT pk_username FROM user WHERE pk_username ='".$_GET["id"]."'");
                            $sql->execute();
                            $result=$sql->get_result();
                            while($row=$result->fetch_assoc()){
                            print($row["pk_username"]); //fetch username for editing
                            }} ?>">
        <input type="password" name="password" placeholder="Password" value="<?php if(isset($_GET["action"]) && $_GET["action"] === "userEdit"){$sql = $conn -> prepare("SELECT password FROM user WHERE pk_username ='".$_GET["id"]."'");
                            $sql->execute();
                            $result=$sql->get_result();
                            while($row=$result->fetch_assoc()){
                            print($row["password"]); //fetch password for editing
                            }} ?>">
        <input type="text" name="firstName" placeholder="First Name" value="<?php if(isset($_GET["action"]) && $_GET["action"] === "userEdit"){$sql = $conn -> prepare("SELECT firstName FROM user WHERE pk_username ='".$_GET["id"]."'");
                            $sql->execute();
                            $result=$sql->get_result();
                            while($row=$result->fetch_assoc()){
                            print($row["firstName"]); //fetch first name for editing
                            }} ?>">
        <Br>
        <input type="text" name="lastName" placeholder="Last Name" value="<?php if(isset($_GET["action"]) && $_GET["action"] === "userEdit"){$sql = $conn -> prepare("SELECT lastName FROM user WHERE pk_username ='".$_GET["id"]."'");
                            $sql->execute();
                            $result=$sql->get_result();
                            while($row=$result->fetch_assoc()){
                            print($row["lastName"]); //fetch last name for editing
                            }} ?>">
        <input type="text" name="email" placeholder="...@service.xyz" value="<?php if(isset($_GET["action"]) && $_GET["action"] === "userEdit"){$sql = $conn -> prepare("SELECT email FROM user WHERE pk_username ='".$_GET["id"]."'");
                            $sql->execute();
                            $result=$sql->get_result();
                            while($row=$result->fetch_assoc()){
                            print($row["email"]); //fetch email for editing
                            }} ?>">
        <input type="checkbox" name="admin" value="admin">
        <br>
        <input type="submit" name="updateUser" value="Update User"> <input type="submit" name="newUser" value="Create User">
    </form>
    <?php
        $query = "SELECT *
                FROM station
                ";
        $sql = $conn -> prepare($query);
        $sql -> execute();
        $result = $sql->get_result();
                if(mysqli_num_rows($result)>0){
                    print("<table>");
                    print("<tr><th>Serial Number</th><th>Station Name</th><th>Station Description</th></tr>"); //display stations
                    while($row = $result -> fetch_assoc()){
                        print("<tr><td>".$row["pk_serialNumber"]."</td><td>".$row["name"]."</td><td>".$row["description"]."</td><td><a href='adminpage.php?action=editStation&id=".$row["pk_serialNumber"]."'>Edit Station</a></td><td><a href='adminpage.php?action=deleteStation&id=".$row["pk_serialNumber"]."'>Delete Station</a></td></tr>");
                    }
                }
    ?>
    <form method = "POST">
        <input type="text" name="serialNum" placeholder="Serial Number" value="<?php if(isset($_GET["action"]) && $_GET["action"] === "editStation"){$sql = $conn -> prepare("SELECT pk_serialNumber FROM station WHERE pk_serialNumber ='".$_GET["id"]."'");
                            $sql->execute();
                            $result=$sql->get_result();
                            while($row=$result->fetch_assoc()){
                            print($row["pk_serialNumber"]); //fetch serial number for editing
                            }} ?>">
        <input type="text" name="statName" placeholder="Station Name" value="<?php if(isset($_GET["action"]) && $_GET["action"] === "editStation"){$sql = $conn -> prepare("SELECT name FROM station WHERE pk_serialNumber ='".$_GET["id"]."'");
                            $sql->execute();
                            $result=$sql->get_result();
                            while($row=$result->fetch_assoc()){
                            print($row["name"]); //fetch station name for editing
                            }} ?>">
        <input type="text" name="statDesc" placeholder="Description" value="<?php if(isset($_GET["action"]) && $_GET["action"] === "editStation"){$sql = $conn -> prepare("SELECT description FROM station WHERE pk_serialNumber ='".$_GET["id"]."'");
                            $sql->execute();
                            $result=$sql->get_result();
                            while($row=$result->fetch_assoc()){
                            print($row["description"]); //fetch station description for editing
                            }} ?>">
        <br>
        <input type="submit" name="updateStation" value="Update Station"> <input type="submit" name="newStation" value="Create Station">
    </form>
        <table>
            <tr><th>Collection</th><th>Description</th></tr>
            <?php $sql = $conn -> prepare("SELECT * FROM collection"); //output user's collections
                    $sql -> execute();
                    $result = $sql -> get_result();
                    if(mysqli_num_rows($result) > 0){
                        while($rows = $result -> fetch_assoc()){ //display collections
                            print("<tr><td>".$rows["name"]."</td><td>".$rows["description"]."</td><td><a href='adminpage.php?action=editCol&id=".$rows['pk_collection']."'>Edit</a></td><td><a href='adminpage.php?action=deleteCol&id=".$rows['pk_collection']."'>Delete</a></td></tr>");
                        }
                    }?>
        </table>
        <form method = "POST">
            <input type="text" name="colName" placeholder="Collection Name" value="<?php if(isset($_GET["action"]) && $_GET["action"] === "editCol"){$sql = $conn -> prepare("SELECT name FROM collection WHERE pk_collection ='".$_GET["id"]."'");
                            $sql->execute();
                            $result=$sql->get_result();
                            while($row=$result->fetch_assoc()){
                            print($row["name"]); //fetch station name for editing
                            }} ?>">
            <input type="text" name="colDesc" placeholder="Description" value="<?php if(isset($_GET["action"]) && $_GET["action"] === "editCol"){$sql = $conn -> prepare("SELECT description FROM collection WHERE pk_collection ='".$_GET["id"]."'");
                            $sql->execute();
                            $result=$sql->get_result();
                            while($row=$result->fetch_assoc()){
                            print($row["description"]); //fetch collection description for editing
                            }} ?>">
            <input type="submit" name="editCol" value="Edit Collection"><input type="submit" name="createCol" value="Create Collection">
        <br>
        <form method="POST">
                <label>Select Record to add to the collection: </label><select name = "record">
                    <?php $sql = $conn -> prepare("SELECT * FROM measurement"); //module to select measurement record
                    $sql -> execute();
                    $result = $sql -> get_result();
                    if(mysqli_num_rows($result) > 0){
                        while($rows = $result -> fetch_assoc()){
                            print("<option value=".$rows["pk_measurement"].">".$rows["timestamp"]."</option>");
                        }
                    }?>
                </select>
                <br>
                <label>Select collection to add to: </label><select name = "collection">
                    <?php $sql = $conn -> prepare("SELECT * FROM collection WHERE fk_user_creates = ?"); //module to select collection
                    $sql -> bind_param("s",$username);
                    $sql -> execute();
                    $result = $sql -> get_result();
                    if(mysqli_num_rows($result) > 0){
                        while($rows = $result -> fetch_assoc()){
                            print("<option value=".$rows["pk_collection"].">".$rows["name"]."</option>");
                        }
                    }?>
                </select>
                <input type = "Submit" name = "addToCollection" value = "Add to Collection">
            </form>
            <br>
            <h2>Share your Collection to the users here:</h2>
            <form method = "POST">
            <label>Choose your collection: </label><select name = "collection-share">
                        <?php $sql = $conn -> prepare("SELECT * FROM collection WHERE fk_user_creates = ?"); //module to share collection with friends
                        $sql -> bind_param("s",$username);
                        $sql -> execute();
                        $result = $sql -> get_result();
                        if(mysqli_num_rows($result) > 0){
                            while($rows = $result -> fetch_assoc()){
                                print("<option value=".$rows["pk_collection"].">".$rows["name"]."</option>");
                            }
                        }?>
                </select>
            <label>Choose the friend with whom you want to share: </label><select name = "user-share">
                        <?php $sql = $conn -> prepare("SELECT pk_username FROM user WHERE pk_username IN 
                                                        (SELECT pkfk_user_friend FROM isfriend WHERE pkfk_user_user = ?
                                                        UNION
                                                        SELECT pkfk_user_user FROM isfriend WHERE pkfk_user_friend = ?)"); //get list of friends
                        $sql -> bind_param("ss",$username,$username);
                        $sql -> execute();
                        $result = $sql -> get_result();
                        if(mysqli_num_rows($result) > 0){
                            while($rows = $result -> fetch_assoc()){
                                print("<option value=".$rows["pk_username"].">".$rows["pk_username"]."</option>");
                            }
                        }?>
                </select>
            <input type = "submit" name = "shareCol" value = "Share Your Collection">
            </form>
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['shareCol'])) {
                // 1. Grab values from the form
                $collectionID = $_POST['collection-share'];
                $friendUsername = $_POST['user-share'];

                // 2. Check if this person already has access to prevent primary key errors
                $check = $conn->prepare("SELECT * FROM hasaccess WHERE pkfk_user = ? AND pkfk_collection = ?");
                $check->bind_param("si", $friendUsername, $collectionID);
                $check->execute();
                $existing = $check->get_result();

                if ($existing->num_rows > 0) {
                    echo "<p style='color: orange;'>This friend already has access to that collection!</p>";
                } else {
                    // 3. Insert into the hasaccess table
                    $stmt = $conn->prepare("INSERT INTO hasaccess (pkfk_user, pkfk_collection) VALUES (?, ?)");
                    $stmt->bind_param("si", $friendUsername, $collectionID);

                if ($stmt->execute()) {
                    echo "<p style='color: green;'>Collection successfully shared with " . htmlspecialchars($friendUsername) . "!</p>";
                } else {
                    echo "<p style='color: red;'>Error sharing collection</p>";
                }
        $stmt->close();
    }
    $check->close();
}?>
    <h2>All Measurements in the System:</h2>
            <br>
    <?php
    $query = "SELECT *
                FROM measurement
                ";
        if(isset($_GET["filter"])){
            $query .= " ORDER BY timestamp DESC;";
        }
        $sql = $conn -> prepare($query);
        $sql -> execute();
        $result = $sql->get_result();
                if(mysqli_num_rows($result)>0){
                    print("<table>");
                    print("
                        <tr><th>Measurement</th>
                        <th>Station</th>
                        <th><a href='adminpage.php?filter=1'>Timestamp</a></th>
                        <th>Temperature</th><th>Humidity</th>
                        <th>Light Intensity</th>
                        <th>Air Quality</tr>");
                    while($row = mysqli_fetch_assoc($result)){ //display measurements
                        print("<tr>");
                        print("<td>".$row["pk_measurement"]."</td><td>".$row["fk_station_records"]."</td><td>".$row["timestamp"]."</td>"."<td>".$row["temperature"]."</td>"."<td>".$row["humidity"]."</td>"."<td>".$row["light"]."</td>"."<td>".$row["gas"]."</td><td><a href='adminpage.php?deleteRecord=".$row["pk_measurement"]."'>Delete</a></th></tr>"); //delete measurement link
                        print("</tr>");
                    }
                    print("</table>");
                    print("<br></br>");
                }
                ?>
</div>
</body>
<footer>
    <?php include "footer.php"; ?>
</footer>
</html>