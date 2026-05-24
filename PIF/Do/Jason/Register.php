<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="stylesheet" type="text/css" href="MyCss.css?<?=time();?>">
    <title>Register</title>
</head>
<body>
    <?php 
   include_once("commonphp.php");
    ?>
    <h1 class="Title"> Register</h1>
<p class="Title">Create an account for yourself.</p>
<div class="Title">
     <form method="post" >
        <input class="regInput" type="text" name="firstname" placeholder="First name" required>
        <input class="regInput" type="text" name="lastname" placeholder="Last name" required><br>
        <input class="regInput" type="text" name="username" placeholder="Username" required>
        <input class="regInput" type="email" name="email" placeholder="Email" required> <br>
        <input class="regInput" type="password" name="password" placeholder="Password" required>
        <input class="regInput" type="password" name="password2" placeholder="Confirm password" required> <br>
        <button class="regBtn" type="submit" name="RegisterBtn">Register</button>

    </form>
</div>

<p class="Title"> If you already have an account please login here.</p>
 <a href="index.php" class ="Title"> Login </a> 


   <div class="formText">
        <?php
 
         // Process registration for PIF.User table: (full_name, administrator, email_address, friends, Upswd, UName)
         if (isset($_POST['RegisterBtn'])) {
             $firstname = trim($_POST['firstname'] ?? '');
             $lastname = trim($_POST['lastname'] ?? '');
             $username = trim($_POST['username'] ?? '');
             $email = trim($_POST['email'] ?? '');
             $password = $_POST['password'] ?? '';
             $password2 = $_POST['password2'] ?? '';
 
             $full_name = trim($firstname . ' ' . $lastname);
 
             if ($full_name === '' || $username === '' || $email === '' || $password === '' || $password2 === '') {
                 echo '<div class="addedProduct">Please fill in all required fields.</div>';
             } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                 echo '<div class="addedProduct">Please enter a valid email address.</div>';
             } elseif ($password !== $password2) {
                 echo '<div class="addedProduct">Passwords do not match.</div>';
             } else {
                 // Check duplicate username first
                 $ok = true;
                 if ($stmt = $conn->prepare('SELECT user_ID FROM `User` WHERE UName = ?')) {
                     $stmt->bind_param('s', $username);
                     $stmt->execute();
                     $stmt->store_result();
                     if ($stmt->num_rows > 0) {
                         echo '<div class="addedProduct">This username is already taken.</div>';
                         $ok = false;
                     }
                     $stmt->close();
                 } else {
                     echo '<div class="addedProduct">Database error. Please contact the administrator.</div>';
                     $ok = false;
                 } 
                 if ($ok) {
                     // Then check duplicate email
                     if ($stmt2 = $conn->prepare('SELECT user_ID FROM `User` WHERE email_address = ?')) {
                         $stmt2->bind_param('s', $email);
                         $stmt2->execute();
                         $stmt2->store_result();
                         if ($stmt2->num_rows > 0) {
                             echo '<div class="addedProduct">An account with that email already exists.</div>';
                             $stmt2->close();
                             $ok = false;
                         }
                         $stmt2->close();
                     } else {
                         echo '<div class="addedProduct">Database error. Please contact the administrator.</div>';
                         $ok = false;
                     }
                 }

                 if ($ok) {
                     // Hash the password and insert new user with administrator = 0 and friends = NULL, include UName
                     $passHash = password_hash($password, PASSWORD_DEFAULT);
                     if ($ins = $conn->prepare('INSERT INTO `User` (full_name, administrator, email_address, friends, Upswd, UName) VALUES (?, 0, ?, NULL, ?, ?)')) {
                         $ins->bind_param('ssss', $full_name, $email, $passHash, $username);
                         if ($ins->execute()) {
                             $newUserId = $conn->insert_id;
                             // Create an empty friendlist for the new user and update the user's friends pointer.
                             if ($friendStmt = $conn->prepare('INSERT INTO Friendlist (`user`) VALUES (?)')) {
                                 $friendStmt->bind_param('i', $newUserId);
                                 if ($friendStmt->execute()) {
                                     $friendlistId = $conn->insert_id;
                                     $friendStmt->close();
                                     $updateStmt = $conn->prepare('UPDATE `User` SET friends = ? WHERE user_ID = ?');
                                     if ($updateStmt) {
                                         $updateStmt->bind_param('ii', $friendlistId, $newUserId);
                                         $updateStmt->execute();
                                         $updateStmt->close();
                                     }
                                 }
                             }
                             echo '<div class="addedProduct">You successfully registered!</div>';
                         } else {
                             echo '<div class="addedProduct">Registration failed. Please try again later.</div>';
                         }
                         $ins->close();
                     } else {
                         echo '<div class="addedProduct">Database error. Please contact the administrator.</div>';
                     }
                 }
             }
         }
  
         ?>
    </div>
</body>
</html>