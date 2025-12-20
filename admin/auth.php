<?php
/**
 * Admin Authentication Check
 * Include this at the top of all admin pages
 */

session_start();

// Initialize admin debug collector
if (!isset($GLOBALS['ADMIN_DEBUG']) || !is_array($GLOBALS['ADMIN_DEBUG'])) {
    $GLOBALS['ADMIN_DEBUG'] = [];
}
function admin_debug($msg) { $GLOBALS['ADMIN_DEBUG'][] = $msg; }

admin_debug('🔍 [Auth] Session started');
admin_debug('🔍 [Auth] Logged in: ' . (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true ? 'true' : 'false'));
admin_debug('🔍 [Auth] Username: ' . (isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : '(none)'));

// Optional debug bypass: enable admin without credentials via query param
$skip_auth = (isset($_GET['noauth']) && $_GET['noauth'] === '1');
if ($skip_auth) {
    $_SESSION['admin_logged_in'] = true;
    if (!isset($_SESSION['admin_username'])) {
        $_SESSION['admin_username'] = 'debug';
    }
    $_SESSION['admin_debug_mode'] = true;
    admin_debug('⚠️ [Auth] Bypass enabled (noauth=1). Running without credentials.');
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    admin_debug('⚠️ [Auth] Not logged in, redirecting to /admin/index.php');
    header('Location: /admin/index.php');
    exit;
}

// Auto-logout after 2 hours of inactivity
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
    session_unset();
    session_destroy();
    admin_debug('⚠️ [Auth] Auto-logout due to inactivity');
    header('Location: /admin/index.php');
    exit;
}

$_SESSION['last_activity'] = time();
admin_debug('✅ [Auth] Session active, last_activity updated');

// Include main config
admin_debug('🔍 [Auth] Loading config and helpers');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db-functions.php';

// DB ping
try {
    if (isset($pdo) && $pdo instanceof PDO) {
        $pdo->query('SELECT 1');
        admin_debug('✅ [Auth] DB ping OK');
    } else {
        admin_debug('❌ [Auth] $pdo not set or invalid');
    }
} catch (PDOException $e) {
    admin_debug('❌ [Auth] DB ping failed: ' . $e->getMessage());
}
