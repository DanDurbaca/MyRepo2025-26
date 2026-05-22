<?php
session_start();
if(!isset($_SESSION["username"])){
    header("location:login.php");
}
else{
    $usrname = $_SESSION["username"];
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
    <?php
        if(isset($usrname)){
        print("<h1>$usrname's stations</h1>");
        $sql="SELECT pk_username FROM user WHERE pk_username ='$usrname'";
        $result = mysqli_query($conn,$sql);
            if(mysqli_num_rows($result)>0){
                $rows = mysqli_fetch_assoc($result);
                $id = $rows["pk_username"];
                $sql = "SELECT pk_serialNumber, name, description FROM station WHERE fk_user_owns = '$id'"; //get stations owned by current user
                $result = mysqli_query($conn,$sql);
                if(mysqli_num_rows($result)>0){
                    print("<table>"); //prepare table for station data
                    print("
                        <tr>
                        <th>Serial Number</th>
                        <th>Name</th>
                        <th>Description</th>
                        </tr>
                        ");
                    while($row = mysqli_fetch_assoc($result)){ //output station data
                        print("<tr>");
                        print("<td>".$row["pk_serialNumber"]."</td>"."<td>".$row["name"]."</td>"."<td>".$row["description"]."</td>"."<td><a href=stations.php?action=edit&id=".$row["pk_serialNumber"].">Edit</a></td><td><a href=stations.php?action=delete&id=".$row["pk_serialNumber"].">Delete</a></td>");
                        print("</tr>");
                    }
                    print("</table>");
                    print("<br></br>");
                    ?><h2><a href="stations.php?action=add">Add New Station?</a></h2><?php
                }
                else{
                    print("<h2>No stations found,register one?</h2>");
                    print("<form method='POST'>
                            <p>Enter Serial number of your station: <input type='text' name='newStation' value='ST-'></p>
                            <p><input type='Submit' name = 'newStationCr' value = 'Add new station'></p>
                           </form>");
                    if($_SERVER["REQUEST_METHOD"] == "POST"){
                        $serNum == $_POST["newStation"];
                        $res = addstation($conn,$serNum,$id);
                        print("<h3>$res</h3>");
                        header("Location:stations.php");
                    }
                }
                ?>
                <?php
                if(isset($_GET["action"])){ //handle station actions
                    $action = $_GET["action"];
                    if($action != "add"){
                        $serial = $_GET["id"];
                    }
                    if($_SERVER["REQUEST_METHOD"]=="POST" && $action === 'edit' && isset($serial)){ //handle station edit
                        $newStationName = $_POST["StationName"];
                        $newDescription = $_POST["Description"];
                        if(isset($newStationName)){
                            $queryType = "update";
                            $res = queriesUserStation($conn,$newStationName,$newDescription,$serial,$queryType); //update station info
                            header("Location:stations.php");
                        }
                    }
                    elseif($action === 'delete'){
                        print("<p>Are you sure you want to delete your Station (this loss is permanent and will not be recoverable)</p>"); //deletion confirmation
                        print("<a href=stations.php?action=delete&id=$serial&confirm=yes>Confirm deletion of $serial station</a>");
                        print("<br></br>");
                        print("<a href=stations.php?action=delete&id=$serial&confirm=no>Do not delete station</a>");
                        if(isset($_GET["confirm"])){ //handle deletion confirmation
                            $yesno = $_GET["confirm"];
                            if($yesno === "yes"){
                                queriesUserStation($conn,$newStationName,$newDescription,$serial,"delete");
                                header("Location:stations.php");
                            }
                            else{
                                header("Location:stations.php");
                            }
                        }
                    }
                    elseif($action === 'add'){ //handle new station addition
                    print("<form method='POST'>
                            <p>Enter Serial number of your station: <input type='text' name='newStation' value='ST-'></p>
                            <p><input type='Submit' name = 'newStationCr' value = 'Add new station'></p>
                           </form>");
                    if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['newStationCr'])){ //process new station addition
                        $serNum = $_POST["newStation"];
                        $sql = $conn -> prepare("SELECT * FROM station WHERE pk_serialNumber = ? AND fk_user_owns IS NULL"); //check if station exists and is unowned
                        $sql -> bind_param("s",$serNum);
                        $sql -> execute();
                        $result=$sql->get_result();
                        if(mysqli_num_rows($result)>0){
                            $noOwner = TRUE; //station is unowned
                        }
                        if(isset($noOwner) && $noOwner == TRUE){ //proceed with addition
                            $res = addstation($conn,$serNum,$id); //add station
                            print("<h3>$res</h3>");
                            header("Location:stations.php");
                        }
                    }
                }
                }
                ?>
                    <h2>Editing the station</h2>
                    <form method='POST'>
                            <p>Serial Number: <input type="text" name="SerialNum" disabled='disabled' value='<?php if(isset($_GET["action"]) && $action === "edit"){print($serial);} //get SerialNumber that should not be changed ?>'></p>
                            <p>Station Name: <input type="text" name="StationName" value='<?php if(isset($_GET["action"]) && $action === "edit"){$sql = $conn -> prepare("SELECT name FROM station WHERE pk_serialNumber ='$serial'");
                            $sql->execute();
                            $result=$sql->get_result();
                            while($row=$result->fetch_assoc()){ //get current station name
                            print($row["name"]);
                            }} ?>'></p>
                            <p>Description: <input type="text" name="Description" value='<?php if(isset($_GET["action"]) && $action === "edit"){$sql = $conn -> prepare("SELECT description FROM station WHERE pk_serialNumber ='$serial'");
                            $sql->execute();
                            $result=$sql->get_result();
                            while($row=$result->fetch_assoc()){ //get current station description
                            print($row['description']);
                            }} ?>'></p>
                            <p><input type="Submit" value="Update Station"></p>
                    </form>
                <?php
                if(isset($res)){
                    print("<p>$res</p>");
                }
            }
        }
        else{
            mysqli_error($sql);
        }
    ?>
</body>
<footer>
    <?php include "footer.php"; ?>
</footer>
</html>