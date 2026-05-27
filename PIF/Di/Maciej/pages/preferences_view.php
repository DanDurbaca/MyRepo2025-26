<?php
include __DIR__ . '/../includes/header.php'; // Include site header/navigation

// Get current theme from session, default to 'light'
$currentTheme = $_SESSION['theme'] ?? 'light';
?>

<div class="container preferences-page">

    <!-- Page heading -->
    <h1>Preferences</h1>

    <!-- Preferences form -->
    <form method="post" action="<?= BASE_URL ?>/controller/preferences.php">

        <!-- Theme selection -->
        <div class="form-group">
            <label for="theme">Choose Theme:</label>
            <select name="theme" id="theme">
                <!-- Light mode option, selected if current theme is light -->
                <option value="light" <?= $currentTheme === 'light' ? 'selected' : '' ?>>Light Mode</option>

                <!-- Dark mode option, selected if current theme is dark -->
                <option value="dark" <?= $currentTheme === 'dark' ? 'selected' : '' ?>>Dark Mode</option>
            </select>
        </div>

        <!-- Submit button -->
        <button type="submit" class="btn btn-primary">Save Preferences</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?> <!-- Include site footer -->
