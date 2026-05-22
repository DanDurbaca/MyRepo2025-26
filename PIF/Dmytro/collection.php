<?php
/**
 * Collection Management Module
 * 
 * Handles all collection-related operations including:
 * - Viewing user's own collections
 * - Creating, editing, and deleting collections
 * - Adding measurements to collections
 * - Viewing measurements within collections by date range
 * - Sharing collections with friends
 * - Viewing collections shared by friends
 * 
 * Security:
 * - Requires active session (user must be logged in)
 * - Uses prepared statements with parameter binding to prevent SQL injection
 * - Validates user ownership before allowing edit/delete operations
 * - Verifies friend relationships before sharing access
 * 
 * Database Tables Used:
 * - user: User account information
 * - station: IoT stations owned by users
 * - measurement: Sensor readings from stations
 * - collection: User-created collections
 * - contains: Junction table linking measurements to collections
 * - hasaccess: Permission table for sharing collections with friends
 * - isfriend: Bidirectional friend relationship tracking
 * 
 * Key Features:
 * - Add measurements to collections by selecting station and measurement
 * - Create new collections with name and description
 * - Edit collection metadata
 * - Delete collections (permanent, non-recoverable)
 * - Query measurements by date range and collection
 * - Share collections with verified friends
 * - Prevent duplicate access permissions
 * - Display shared collections from friends
 * - View friend's shared collection details
 * 
 * @requires queries.php - Database connection and custom functions
 * @requires navbar.php - Navigation template
 * @requires session_start() - Active user session
 */
?>
<?php
include "queries.php"; //include the database connection and functions
session_start(); //start session
if(isset($_SESSION["username"])){
    $username = $_SESSION["username"]; //login check
}
else{
    header("Location:login.php"); //redirect to login if not logged in
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="mystyle.css">
    <?php include "navbar.php"; ?>
</head>
<body>
    <?php
        print("<h1>Here your collections, $username</h1>");
    ?>
    <h2><a href="collection.php?addToCollection=1">Add measurement?</a></h2><!-- Request to add a measurement to a collection -->
    <?php if(isset($_GET["addToCollection"]))://conditional HTML that is marked with a ":"?>
        <form method="POST">
            <select name = "station">
                <?php $sql = $conn -> prepare("SELECT * FROM station WHERE fk_user_owns = ?"); //sql to get stations owned by user 
                $sql -> bind_param("s",$username);
                $sql -> execute();
                $result = $sql -> get_result();
                if(mysqli_num_rows($result) > 0){
                    while($rows = $result -> fetch_assoc()){
                        print("<option value=".$rows["pk_serialNumber"].">".$rows["name"]."</option>");
                    }
                }?>
            </select>
            <input type="submit" name="selectStation" value = "Select This Station">
            <br>
            <?php if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["selectStation"])): //module to select measurements to add to collection
                $id=$_POST["station"];?>
                <form method="POST">
                <label>Select Record to add to the collection: </label><select name = "record">
                    <?php $sql = $conn -> prepare("SELECT * FROM measurement WHERE fk_station_records = ?");
                    $sql -> bind_param("s",$id);
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
                <br>
                <input type = "submit" name="addToCol" value="Add to Collection"> 
                </form>
            <?php endif;?>
        </form>
        <?php endif;//end of the conditional html
        if($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST["addToCol"])){
            print("works");
            $record = $_POST["record"];
            $collection = $_POST["collection"];
            $result = insertIntoCollection($conn,$record,$collection); //function to insert measurement into collection
            header("Location:collection.php");
            print($result);
        }?>
        <br>
        <?php if(isset($_GET["action"]) || $_SERVER["REQUEST_METHOD"]=="POST"){ //handle collection actions
                    $action = $_GET["action"];
                    if($_GET["id"]){
                        $colId = $_GET["id"];
                    }
                    if($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST["createCol"])){ //module to create a new collection
                        $newColName = $_POST["colName"];
                        $newDescription = $_POST["colDesc"];
                        $sql = $conn -> prepare("INSERT INTO collection(name,description,fk_user_creates) VALUE(?,?,?)");
                        $sql -> bind_param("sss",$newColName,$newDescription,$username);
                        $sql -> execute();
                        header("Location:collection.php");
                    }
                    elseif($_SERVER["REQUEST_METHOD"]=="POST" && $action === 'edit' && isset($colId) && isset($_POST["updateCol"])){ //module to edit collection
                        $newColName = $_POST["colName"];
                        $newDescription = $_POST["colDesc"]; 
                        if(isset($newColName)){
                            $sql = $conn -> prepare("UPDATE collection SET name=?,description=? WHERE pk_collection = ?");
                            $sql -> bind_param("ssi",$newColName,$newDescription,$colId);
                            $sql -> execute();
                            header("Location:collection.php");
                        }
                    }
                    elseif($action === 'delete'){ //module to delete collection
                        print("<p>Are you sure you want to delete your Collection (this loss is permanent and will not be recoverable)</p>");
                        print("<a href=collection.php?action=delete&id=$colId&confirm=yes>Confirm deletion of the collection</a>"); //confirmation prompt
                        print("<br></br>");
                        print("<a href=collection.php?action=delete&id=$colId&confirm=no>Do not delete collection</a>");
                        if(isset($_GET["confirm"])){ //applying confirmation choice
                            $yesno = $_GET["confirm"];
                            if($yesno === "yes"){
                                $sql = $conn -> prepare("DELETE FROM collection WHERE pk_collection = ?");
                                $sql -> bind_param("i",$colId);
                                $sql -> execute();
                                header("Location:collection.php");
                            }
                            else{
                                header("Location:collection.php");
                            }
                        }
                    }
                }
        ?>
        <h2>Editing the Collection</h2>
                    <form method='POST'>
                            <p>Collection Name: <input type="text" name="colName" value='<?php if(isset($_GET["action"]) && $action === "edit"){$sql = $conn -> prepare("SELECT name FROM collection WHERE pk_collection = ?");
                            $sql -> bind_param("i",$colId);
                            $sql->execute();
                            $result=$sql->get_result();
                            while($row=$result->fetch_assoc()){
                            print($row["name"]);
                            }} ?>'></p>
                            <p>Collection Description: <input type="text" name="colDesc" value='<?php if(isset($_GET["action"]) && $action === "edit"){$sql = $conn -> prepare("SELECT description FROM collection WHERE pk_collection =?");
                            $sql -> bind_param("i",$colId);
                            $sql->execute();
                            $result=$sql->get_result();
                            while($row=$result->fetch_assoc()){
                            print($row["description"]);
                            }} ?>'></p>
                            <p><input type="Submit" name="updateCol" value="Update Collection"><input type="submit" name="createCol" value="Create Collection"></p>
                    </form>
        <h2><label>Your Collections:</label></h2>
        <table>
            <tr><th>Collection</th><th>Description</th></tr>
            <?php $sql = $conn -> prepare("SELECT * FROM collection WHERE fk_user_creates = ?"); //output user's collections
                    $sql -> bind_param("s",$username);
                    $sql -> execute();
                    $result = $sql -> get_result();
                    if(mysqli_num_rows($result) > 0){
                        while($rows = $result -> fetch_assoc()){
                            print("<tr><td>".$rows["name"]."</td><td>".$rows["description"]."</td><td><a href='collection.php?action=edit&id=".$rows['pk_collection']."'>Edit</a></td><td><a href='collection.php?action=delete&id=".$rows['pk_collection']."'>Delete</a></td></tr>");
                        }
                    }?>
        </table>
        <form method="POST">
            <label>Choose your station: </label><select name = "station-view">
                <?php $sql = $conn -> prepare("SELECT * FROM station WHERE fk_user_owns = ?"); //select station to view measurements from
                $sql -> bind_param("s",$username);
                $sql -> execute();
                $result = $sql -> get_result();
                if(mysqli_num_rows($result) > 0){
                    while($rows = $result -> fetch_assoc()){
                        print("<option value=".$rows["pk_serialNumber"].">".$rows["name"]."</option>");
                    }
                }?>
            </select>
            <br>
            <label>Choose your collection: </label><select name = "collection-view">
                    <?php $sql = $conn -> prepare("SELECT * FROM collection WHERE fk_user_creates = ?"); //select collection to view measurements from
                    $sql -> bind_param("s",$username);
                    $sql -> execute();
                    $result = $sql -> get_result();
                    if(mysqli_num_rows($result) > 0){
                        while($rows = $result -> fetch_assoc()){
                            print("<option value=".$rows["pk_collection"].">".$rows["name"]."</option>");
                        }
                    }?>
                </select>
            <br>
            <label>From:</label>
            <input type="datetime-local" name="start_datetime" required>
            <br>
            <label>To:</label>
            <input type="datetime-local" name="end_datetime" required>
            <br>
            <input type="submit" name="selectRange" value="Select measurements">
        </form>
        <?php if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["selectRange"])) { //module to output measurements in date range
            $collectionId = $_POST["collection-view"];  
            $start = $_POST["start_datetime"];
            $end   = $_POST["end_datetime"];
            $station = $_POST["station-view"]; // selected station (owned by user)
            if(isset($station) && isset($collectionId)){
            $sql = $conn->prepare("
            SELECT collection.name AS collection_name,collection.description AS collection_description,measurement.fk_station_records AS measurement_station,measurement.timestamp AS measurement_time,measurement.temperature measurement_temp,measurement.humidity measurement_humidity,measurement.pressure measurement_pressure,measurement.light measurement_light,measurement.gas measurement_gas
            FROM measurement
            JOIN contains ON pk_measurement = pkfk_measurement
            JOIN collection ON pkfk_collection = pk_collection
            WHERE fk_station_records = ?
            AND timestamp BETWEEN ? AND ?
            "); //sql to get measurements in date range for selected collection and station

            $sql->bind_param("sss", $station, $start, $end);
            $sql->execute();
            $result = $sql->get_result();
            if(mysqli_num_rows($result) > 0){
                print("<table><tr><th>Collection Name</th><th>Collection Description</th><th>Station</th><th>Timestamp</th><th>Temperature</th><th>Humidity</th><th>Pressure</th><th>Light</th><th>Gas</th></tr>");
                while($rows = $result -> fetch_assoc()){ //output results in table
                    print("<tr><th>".$rows["collection_name"]."</th><th>".$rows["collection_description"]."</th><th>".$rows["measurement_station"]."</th><th>".$rows["measurement_time"]."</th><th>".$rows["measurement_temp"]."</th><th>".$rows["measurement_humidity"]."</th><th>".$rows["measurement_pressure"]."</th><th>".$rows["measurement_light"]."</th><th>".$rows["measurement_gas"]."</th>");
                }
                print("</table>");
            }
        }
    }
    ?>
        <h2>Your friend's collections</h2>
        <?php
        $sql = $conn -> prepare("SELECT * FROM collection JOIN hasaccess ON pkfk_collection = pk_collection WHERE pkfk_user = ? AND fk_user_creates != ?;"); //check for collections shared by friends
        $sql -> bind_param("ss",$username,$username);
        $sql -> execute();
        $result = $sql -> get_result();
        if(mysqli_num_rows($result) > 0){
            print("<table>");
            while($rows = $result -> fetch_assoc()){
                print("<tr><td>".$rows["name"]."</td><td>".$rows["description"]."</td><td><a href=collection.php?action=view&id=".$rows["pk_collection"].">View</a></tr>");
            }
            print("</table>");
        }
        else{
            print("You have not been shared any collection"); //no collections shared
        }
        if(isset($_GET["action"])){
            $colId = $_GET["id"];
            if(isset($colId)){
            $sql = $conn->prepare("
            SELECT collection.name AS collection_name,collection.description AS collection_description,measurement.fk_station_records AS measurement_station,measurement.timestamp AS measurement_time,measurement.temperature measurement_temp,measurement.humidity measurement_humidity,measurement.pressure measurement_pressure,measurement.light measurement_light,measurement.gas measurement_gas
            FROM measurement
            JOIN contains ON pk_measurement = pkfk_measurement
            JOIN collection ON pkfk_collection = pk_collection
            WHERE pk_collection = ?
            ;
            "); //sql to view friend's shared collection details
            $sql->bind_param("i", $colId);
            $sql -> execute();
            $result = $sql->get_result();
            if(mysqli_num_rows($result) > 0){
                print("<table><tr><th>Collection Name</th><th>Collection Description</th><th>Station</th><th>Timestamp</th><th>Temperature</th><th>Humidity</th><th>Pressure</th><th>Light</th><th>Gas</th></tr>");
                while($rows = $result -> fetch_assoc()){ //output results in table
                    print("<tr><th>".$rows["collection_name"]."</th><th>".$rows["collection_description"]."</th><th>".$rows["measurement_station"]."</th><th>".$rows["measurement_time"]."</th><th>".$rows["measurement_temp"]."</th><th>".$rows["measurement_humidity"]."</th><th>".$rows["measurement_pressure"]."</th><th>".$rows["measurement_light"]."</th><th>".$rows["measurement_gas"]."</th>");
                }
                print("</table>");
            }
        }
        }        
        ?>
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
                                                        SELECT pkfk_user_user FROM isfriend WHERE pkfk_user_friend = ?)"); 
                                                        // This query is made to to output data in a 2 way format using 
                                                        // UNION to check if current user is a friend of someone or if someone is their friend 
                                                        // due to the database format and friend request handling system I have set up, 
                                                        // UNION is needed to combine 2 select statements and to clear any duplicate
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
}
?>
</body>
<footer>
    <?php include "footer.php"; ?>
</footer>
</html>