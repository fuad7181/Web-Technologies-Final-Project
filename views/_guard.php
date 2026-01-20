<?php
// Prevent opening view files directly (always go through /public/index.php)
// This avoids broken form actions/paths and helps keep routing consistent.

$script = $_SERVER['SCRIPT_NAME'] ?? '';
$pos = strpos($script, '/views');

// If the current URL is inside /views, redirect to /public/index.php
if ($pos !== false) {
    $root = substr($script, 0, $pos);
    header('Location: ' . $root . '/public/index.php');
    exit;
}
