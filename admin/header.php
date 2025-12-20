<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>Admin - Clase de Ciencia</title>
    <link rel="stylesheet" href="/assets/css/style.css">
        <script>
            // Emit early auth diagnostics if present
            (function(){
                try {
                    var msgs = <?php echo isset($GLOBALS['ADMIN_DEBUG']) ? json_encode($GLOBALS['ADMIN_DEBUG'], JSON_UNESCAPED_UNICODE) : '[]'; ?>;
                    if (Array.isArray(msgs) && msgs.length) {
                        console.log('⚠️ [Admin] Early diagnostics from auth:');
                        msgs.forEach(function(m){ console.log(m); });
                    }
                    console.log('🔍 [Admin] PHP file:', '<?= htmlspecialchars(basename($_SERVER['PHP_SELF']), ENT_QUOTES, 'UTF-8') ?>');
                    console.log('🔍 [Admin] User:', '<?= isset($_SESSION['admin_username']) ? htmlspecialchars($_SESSION['admin_username'], ENT_QUOTES, 'UTF-8') : '(none)' ?>');
                } catch (e) {
                    console.log('❌ [Admin] Diagnostics emit error:', e && e.message);
                }
            })();
        </script>
</head>
<body>
    <!-- SVG sprite for small icons used in admin (kept inline for widest compatibility) -->
    <svg aria-hidden="true" style="position:absolute;width:0;height:0;overflow:hidden;" xmlns="http://www.w3.org/2000/svg">
        <symbol id="icon-leaf" viewBox="0 0 24 24">
            <path d="M20 6c-4 0-8 4-10 6S4 18 4 18" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M20 6c0 4-4 8-8 10" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
        </symbol>
        <symbol id="icon-dashboard" viewBox="0 0 24 24">
            <rect x="3" y="3" width="8" height="8" stroke="currentColor" stroke-width="1.2" fill="none"/>
            <rect x="13" y="3" width="8" height="4" stroke="currentColor" stroke-width="1.2" fill="none"/>
            <rect x="13" y="9" width="8" height="12" stroke="currentColor" stroke-width="1.2" fill="none"/>
            <rect x="3" y="13" width="8" height="8" stroke="currentColor" stroke-width="1.2" fill="none"/>
        </symbol>
        <symbol id="icon-article" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="16" stroke="currentColor" stroke-width="1.2" fill="none" rx="1"/>
            <path d="M7 8h10M7 12h10M7 16h6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
        </symbol>
        <symbol id="icon-flask" viewBox="0 0 24 24">
            <path d="M8 3h8M10 3v4l4 6v5a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1v-5l4-6V3" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
        </symbol>
        <symbol id="icon-chart" viewBox="0 0 24 24">
            <rect x="4" y="10" width="3" height="8" stroke="currentColor" stroke-width="1.2" fill="none"/>
            <rect x="10.5" y="6" width="3" height="12" stroke="currentColor" stroke-width="1.2" fill="none"/>
            <rect x="17" y="3" width="3" height="15" stroke="currentColor" stroke-width="1.2" fill="none"/>
        </symbol>
        <symbol id="icon-folder" viewBox="0 0 24 24">
            <path d="M3 7a2 2 0 0 1 2-2h4l2 2h6a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" stroke="currentColor" stroke-width="1.2" fill="none"/>
        </symbol>
        <symbol id="icon-tag" viewBox="0 0 24 24">
            <path d="M20 10v6a2 2 0 0 1-2 2h-6l-8-8 8-8h6a2 2 0 0 1 2 2z" stroke="currentColor" stroke-width="1.2" fill="none"/>
            <circle cx="9" cy="9" r="1" fill="currentColor"/>
        </symbol>
        <symbol id="icon-calendar" viewBox="0 0 24 24">
            <rect x="3" y="5" width="18" height="16" stroke="currentColor" stroke-width="1.2" fill="none" rx="1"/>
            <path d="M16 3v4M8 3v4M3 11h18" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
        </symbol>
        <symbol id="icon-plus" viewBox="0 0 24 24">
            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
        </symbol>
        <symbol id="icon-edit" viewBox="0 0 24 24">
            <path d="M3 21v-3l11-11 3 3L6 21H3z" stroke="currentColor" stroke-width="1.2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M14 7l3 3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
        </symbol>
        <symbol id="icon-save" viewBox="0 0 24 24">
            <path d="M5 4h14v16H5z" stroke="currentColor" stroke-width="1.2" fill="none"/>
            <path d="M9 4v6h6V4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
        </symbol>
        <symbol id="icon-trash" viewBox="0 0 24 24">
            <path d="M3 6h18" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
            <path d="M8 6v12a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V6" stroke="currentColor" stroke-width="1.2" fill="none" stroke-linecap="round"/>
            <path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
        </symbol>
        <symbol id="icon-close" viewBox="0 0 24 24">
            <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
        </symbol>
        <symbol id="icon-star" viewBox="0 0 24 24">
            <path d="M12 2l2.6 6.6L21 9l-5 3.6L17.2 21 12 17.8 6.8 21 8 12.6 3 9l6.4-0.4L12 2z" stroke="currentColor" stroke-width="0.8" fill="currentColor"/>
        </symbol>
        <symbol id="icon-check" viewBox="0 0 24 24">
            <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="1.6" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
        </symbol>
        <symbol id="icon-list" viewBox="0 0 24 24">
            <path d="M8 6h13M8 12h13M8 18h13" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
            <path d="M3 6h1M3 12h1M3 18h1" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
        </symbol>
    </svg>

    <header class="admin-header">
        <h1 class="site-title"><svg class="logo-icon" width="20" height="20" aria-hidden="true"><use xlink:href="#icon-leaf"/></svg> Clase de Ciencia - Admin
        <?php if (!empty($_SESSION['admin_debug_mode'])): ?>
          <span style="margin-left:0.5rem;padding:0.2rem 0.4rem;background:#ff9800;color:#000;font-size:0.85rem;font-weight:700;">DEBUG (sin credenciales)</span>
        <?php endif; ?>
        </h1>
        <div>
            <a href="/">Ver Sitio</a>
            <a href="/admin/logout.php">Salir (<?= isset($_SESSION['admin_username']) ? htmlspecialchars($_SESSION['admin_username'], ENT_QUOTES, 'UTF-8') : 'anon' ?>)</a>
        </div>
    </header>
    
    <div class="admin-container">
        <aside class="admin-sidebar">
            <nav>
                <ul>
                    <li><a href="/admin/dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>"><svg class="admin-icon" width="16" height="16" aria-hidden="true"><use xlink:href="#icon-dashboard"/></svg> Panel</a></li>
                    <li><a href="/admin/clases/index.php" class="<?= strpos($_SERVER['PHP_SELF'], '/clases/') !== false ? 'active' : '' ?>"><svg class="admin-icon" width="16" height="16" aria-hidden="true"><use xlink:href="#icon-list"/></svg> Clases</a></li>
                    <li><a href="/admin/componentes/index.php" class="<?= strpos($_SERVER['PHP_SELF'], '/componentes/') !== false ? 'active' : '' ?>"><svg class="admin-icon" width="16" height="16" aria-hidden="true"><use xlink:href="#icon-flask"/></svg> Componentes</a></li>
                    <li><a href="/admin/kits/index.php" class="<?= strpos($_SERVER['PHP_SELF'], '/kits/') !== false ? 'active' : '' ?>"><svg class="admin-icon" width="16" height="16" aria-hidden="true"><use xlink:href="#icon-folder"/></svg> Kits</a></li>
                    <li><a href="/admin/contratos/index.php" class="<?= strpos($_SERVER['PHP_SELF'], '/contratos/') !== false ? 'active' : '' ?>"><svg class="admin-icon" width="16" height="16" aria-hidden="true"><use xlink:href="#icon-folder"/></svg> Contratos</a></li>
                    <li><a href="/admin/entregas/index.php" class="<?= strpos($_SERVER['PHP_SELF'], '/entregas/') !== false ? 'active' : '' ?>"><svg class="admin-icon" width="16" height="16" aria-hidden="true"><use xlink:href="#icon-calendar"/></svg> Entregas</a></li>
                    <li><a href="/admin/lotes/index.php" class="<?= strpos($_SERVER['PHP_SELF'], '/lotes/') !== false ? 'active' : '' ?>"><svg class="admin-icon" width="16" height="16" aria-hidden="true"><use xlink:href="#icon-folder"/></svg> Lotes</a></li>
                    <li><a href="/admin/ia/index.php" class="<?= strpos($_SERVER['PHP_SELF'], '/ia/') !== false ? 'active' : '' ?>"><svg class="admin-icon" width="16" height="16" aria-hidden="true"><use xlink:href="#icon-chart"/></svg> IA</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="admin-main">
