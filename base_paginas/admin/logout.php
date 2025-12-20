<?php
/**
 * Admin Logout
 */

session_start();
session_unset();
session_destroy();

header('Location: /admin/index.php');
exit;
