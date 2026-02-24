<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="ShopStyles.css?<?= time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>

<body>
    <?php
    include_once("CommonCode.php");
    NavigationBar("Login");
    ?>
    
    <div class="login-container">
        <h1><?= t('login_title') ?></h1>
        
        <?php
        $showForm = true;
        $errorMessage = "";
        $successMessage = "";

        if (isset($_POST["username"], $_POST["password"])) {
            $username = $_POST["username"];
            $password = $_POST["password"];
            $fileClients = fopen("Clients.csv", "r");
            $found = false;
            
            while (!feof($fileClients)) {
                $line = fgets($fileClients);
                $clientData = explode(";", trim($line));
                
                if (count($clientData) >= 2) {
                    if ($clientData[0] == $username && $clientData[1] == $password) {
                        $found = true;
                        $_SESSION['logged_in_user'] = $username;
                        $successMessage = t('login_success') . " " . $username . "!";
                        $showForm = false;
                        break;
                    }
                }
            }
            fclose($fileClients);
            
            if (!$found && $showForm) {
                $errorMessage = t('login_error');
            }
        }
        
        if ($successMessage) {
            ?>
            <div class="success-message">
                <p><?= $successMessage ?></p>
                <p><a href="Home.php"><?= t('nav_home') ?></a></p>
            </div>
            <?php
        }
        
        if ($showForm) {
            if ($errorMessage) {
                ?>
                <div class="error-message">
                    <p><?= $errorMessage ?></p>
                </div>
                <?php
            }
            ?>
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label><?= t('login_username') ?></label>
                    <input type="text" name="username" required>
                </div>
                
                <div class="form-group">
                    <label><?= t('login_password') ?></label>
                    <input type="password" name="password" required>
                </div>
                
                <input type="submit" value="<?= t('login_submit') ?>" class="submit-btn">
            </form>
            <?php
        }
        ?>
    </div>
    
</body>

</html>
