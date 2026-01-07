<?php
// includes/header.php

// Ensure config and functions are loaded
require_once 'config.php';
require_once 'functions.php';

// Set default title and css if not provided
$pageTitle = $pageTitle ?? 'Platforma Y';
$cssFile = $cssFile ?? 'css/style.css';
$bodyClass = $bodyClass ?? ''; // Allows for page-specific body classes

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="<?php echo $cssFile; ?>">
    <script>
        // Wczytaj zapisany tryb przed renderowaniem strony
        (function() {
            const theme = localStorage.getItem('theme') || 'dark';
            if (theme === 'light') {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>
</head>
<body class="<?php echo $bodyClass; ?>">
