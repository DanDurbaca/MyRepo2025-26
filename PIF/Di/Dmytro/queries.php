<?php
$local = "localhost";
$username = "root";
$password = "";
$dbname = "portableindoorfeedback";
$conn = mysqli_connect($local,$username,$password,$dbname);
if($conn = mysqli_connect($local,$username,$password,$dbname)){ //connection check

}
else{
    mysqli_connect_error($conn);
}

function queriesUser($connection, $uName, $passWrd, $firstName, $lName, $emName, $qType){ //user registration
    if($qType == "insert"){
        $sql = "INSERT INTO user(pk_userName, firstName, lastName, password, email, role) VALUES('$uName','$firstName','$lName','$passWrd','$emName','User');";
        if(mysqli_query($connection, $sql)){
            $confirmReg = "Your account was created. Log-In <a href='login.php?logout=1'>here</a>";
            return $confirmReg;
        }
        else{
            mysqli_error($conn);
        }
    }
}

function queriesUserStation($connection,$statName,$statDescription,$Snum,$qType){
    //updating station information
    if($qType === "update"){
        $sql = "UPDATE station SET name = '$statName', description = '$statDescription' WHERE pk_serialNumber = '$Snum';";
        if(mysqli_query($connection, $sql)){
            $confirm = "Your station has been successfully updated";
            return $confirm;
        }
        else{
            mysqli_error($conn);
        }
    //deleting the station
    }elseif($qType === "delete"){
        $sql = $conn -> prepare("DELETE FROM station WHERE pk_serialNumber = ?");
        $sql -> bind_param('s',$Snum);
        if($sql -> execute()){
            $confirm = "Your station has been successfully deleted";
            return $confirm;
        }
        else{
            mysqli_error($conn);
        }
    }
}

function addstation($connection, $serialNum, $usrId){ //adding a station to user account
    $sql = $connection -> prepare("UPDATE station SET fk_user_owns = ? WHERE pk_serialNumber = ?");
    $sql -> bind_param("ss",$usrId,$serialNum);
    if($sql -> execute()){
        $confirm = "Your station has been successfully added";
        return $confirm;
    }
    else{
        mysqli_error($conn);
    }
}

function queriesUserManagement($connection,$newUserName,$newPassword,$newEmail,$newFirstName,$newLastName,$userId){ //updating user account information
    $sql = "UPDATE user SET pk_username = '$newUserName', password = '$newPassword', firstName = '$newFirstName', lastName = '$newLastName', email = '$newEmail' WHERE pk_username = '$userId';";
    if(mysqli_query($connection, $sql)){
        $confirm = "Your account has been successfully updated";
        return $confirm;
    }
    else{
        mysqli_error($conn);
    }
}

function insertIntoCollection($connection,$measureId,$colId){ //adding measurement to collection
    $sql = $connection -> prepare("INSERT INTO contains(pkfk_collection,pkfk_measurement) VALUES(?,?)");
    $sql -> bind_param("ii",$colId,$measureId);
    if($sql -> execute()){
        $confirm = "Your station has been successfully added to the collection";
        return $confirm;
    }
    else{
        mysqli_error($conn);
    }
}
?>
