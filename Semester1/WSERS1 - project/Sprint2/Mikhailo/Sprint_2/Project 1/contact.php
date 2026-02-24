<?php
include_once("function.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $arrayOfTranslations['Contact'][$language] ?? 'Contact' ?> - OrangeShop</title>
    <link rel="stylesheet" href="style.css?<?php echo time(); ?>">
</head>
<body>

<?php NavigationBar($page="Contact"); ?>

<section class="contact-section">
    <h1><?= $arrayOfTranslations['ContactUs'][$language] ?? 'Contact Us' ?></h1>
    <p><?= $arrayOfTranslations['ContactIntro'][$language] ?? 'We would love to hear from you. Please fill out the form below or reach us using the contact details.' ?></p>

    <div class="contact-container">
        <div class="contact-info">
            <h2><?= $arrayOfTranslations['OurInfo'][$language] ?? 'Our Contact Information' ?></h2>
            <p><strong><?= $arrayOfTranslations['Address'][$language] ?? 'Address' ?>:</strong> 50, rue de Beggen L-1220 Luxembourg</p>
            <p><strong><?= $arrayOfTranslations['Phone'][$language] ?? 'Phone' ?>:</strong> +352 661 702 125</p>
            <p><strong><?= $arrayOfTranslations['Email'][$language] ?? 'Email' ?>:</strong> yusmy875@school.lu</p>
        </div>

        <div class="contact-form">
            <form method="post" action="">
                <label for="name"><?= $arrayOfTranslations['YourName'][$language] ?? 'Your Name' ?>:</label>
                <input type="text" id="name" name="name" required>

                <label for="email"><?= $arrayOfTranslations['YourEmail'][$language] ?? 'Your Email' ?>:</label>
                <input type="email" id="email" name="email" required>

                <label for="message"><?= $arrayOfTranslations['Message'][$language] ?? 'Message' ?>:</label>
                <textarea id="message" name="message" rows="5" required></textarea>

                <input type="submit" value="<?= $arrayOfTranslations['Send'][$language] ?? 'Send Message' ?>">
            </form>

            <?php
            if ($_SERVER["REQUEST_METHOD"] === "POST") {
                echo "<p class='success-msg'>" . ($arrayOfTranslations['Thanks'][$language] ?? 'Thank you for contacting us! We will get back to you soon.') . "</p>";
            }
            ?>
        </div>
    </div>
</section>

<footer>
    <p>&copy; 2025 OrangeShop. <?= $arrayOfTranslations['Rights'][$language] ?? 'All rights reserved.' ?></p>
</footer>

</body>
</html>
