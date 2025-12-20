<?php
/**
 * Admin Authentication Check
 * Include this at the top of all admin pages
 */

session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/index.php');
    exit;
}

// Auto-logout after 2 hours of inactivity
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
    session_unset();
    session_destroy();
    header('Location: /admin/index.php');
    exit;
}

$_SESSION['last_activity'] = time();

// Include main config
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db-functions.php';
