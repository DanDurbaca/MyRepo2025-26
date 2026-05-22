<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - StationHub' : 'StationHub'; ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <?php if (isset($pageCSS)): ?>
        <link rel="stylesheet" href="css/<?php echo $pageCSS; ?>">
    <?php endif; ?>
    
    <!-- Theme Color Meta Tags -->
    <meta name="theme-color" content="#3498db" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#121212" media="(prefers-color-scheme: dark)">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php if (isset($_SESSION['username'])): ?>
            <?php include 'includes/sidebar.php'; ?>
            <?php endif; ?>