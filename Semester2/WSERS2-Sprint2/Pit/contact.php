<?php
include_once("nav.php");

$messageSent = false;
$errorMessage = "";

if (isset($_POST["send_message"])) {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $subject = trim($_POST["subject"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if ($name === "" || $email === "" || $subject === "" || $message === "") {
        $errorMessage = $arrayOfTranslations["ContactFillFields"] ?? "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = $arrayOfTranslations["ContactInvalidEmail"] ?? "Please enter a valid email address.";
    } else {
        $messageSent = true;
    }
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($language, ENT_QUOTES, 'UTF-8') ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= htmlspecialchars($arrayOfTranslations["ContactBtn"] ?? "Contact", ENT_QUOTES, 'UTF-8') ?>
    </title>
    <link rel="stylesheet" href="style.css?<?= time(); ?>">
</head>

<body>
    <?php NavigationBar($arrayOfTranslations["ContactBtn"] ?? "Contact"); ?>

    <header>
        <h1><?= htmlspecialchars($arrayOfTranslations["ContactBtn"] ?? "Contact", ENT_QUOTES, 'UTF-8') ?></h1>
        <h2><?= htmlspecialchars($arrayOfTranslations["ContactSubTitle"] ?? "Send us a message", ENT_QUOTES, 'UTF-8') ?></h2>
    </header>

    <main>
        <section>
            <h3>
                <?= htmlspecialchars($arrayOfTranslations["ContactText"] ?? "You can contact us here:", ENT_QUOTES, 'UTF-8') ?>
                <br>
                schpi505@school.lu
            </h3>
        </section>

        <?php if ($messageSent): ?>
            <section>
                <h3><?= htmlspecialchars($arrayOfTranslations["ContactSuccess"] ?? "Thank you! Your message has been received.", ENT_QUOTES, 'UTF-8') ?></h3>
            </section>
        <?php endif; ?>

        <?php if ($errorMessage !== ""): ?>
            <section>
                <h3><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></h3>
            </section>
        <?php endif; ?>

        <section>
            <h3><?= htmlspecialchars($arrayOfTranslations["ContactFormTitle"] ?? "Contact Form", ENT_QUOTES, 'UTF-8') ?></h3>

            <form method="POST">
                <div><?= htmlspecialchars($arrayOfTranslations["ContactName"] ?? "Name", ENT_QUOTES, 'UTF-8') ?>:</div>
                <input type="text" name="name" required>

                <br><br>

                <div><?= htmlspecialchars($arrayOfTranslations["ContactEmail"] ?? "Email", ENT_QUOTES, 'UTF-8') ?>:</div>
                <input type="email" name="email" required>

                <br><br>

                <div><?= htmlspecialchars($arrayOfTranslations["ContactSubject"] ?? "Subject", ENT_QUOTES, 'UTF-8') ?>:</div>
                <input type="text" name="subject" required>

                <br><br>

                <div><?= htmlspecialchars($arrayOfTranslations["ContactMessage"] ?? "Message", ENT_QUOTES, 'UTF-8') ?>:</div>
                <textarea name="message" rows="5" required></textarea>

                <br><br>

                <input
                    type="submit"
                    name="send_message"
                    value="<?= htmlspecialchars($arrayOfTranslations["ContactSendBtn"] ?? "Send message", ENT_QUOTES, 'UTF-8') ?>">
            </form>
        </section>
    </main>
</body>

</html>