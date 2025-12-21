<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>Admin - Clase de Ciencia</title>
    
    <!-- Google Fonts - Tipografía moderna científica -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/assets/icons/favicon.svg">
    <link rel="alternate icon" href="/assets/icons/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/assets/icons/favicon.svg">
    <meta name="theme-color" content="#1f3c88">
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
        <symbol id="icon-lupa" viewBox="0 0 24 24">
            <!-- Lupa (magnifying glass) para el logo del admin -->
            <circle cx="10" cy="10" r="7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <line x1="15" y1="15" x2="21" y2="21" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
            <circle cx="10" cy="10" r="4" fill="none" stroke="currentColor" stroke-width="0.8" opacity="0.3"/>
            <ellipse cx="8" cy="8" rx="2" ry="3" fill="currentColor" opacity="0.15" transform="rotate(-35 8 8)"/>
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
        <h1 class="site-title"><svg class="logo-icon" width="20" height="20" aria-hidden="true"><use xlink:href="#icon-lupa"/></svg> Clase de Ciencia - Admin
        <?php if (!empty($_SESSION['admin_debug_mode'])): ?>
          <span style="margin-left:0.5rem;padding:0.2rem 0.4rem;background:#ff9800;color:#000;font-size:0.85rem;font-weight:700;">DEBUG (sin credenciales)</span>
        <?php endif; ?>
        </h1>
        <div>
            <button id="btnRefreshSearch" class="btn" style="margin-right:10px;">🔄 Refrescar buscador</button>
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
                    <li><a href="/admin/kits/index.php" class="<?= (strpos($_SERVER['PHP_SELF'], '/kits/') !== false && strpos($_SERVER['PHP_SELF'], '/kits/manuals/') === false) ? 'active' : '' ?>"><svg class="admin-icon" width="16" height="16" aria-hidden="true"><use xlink:href="#icon-folder"/></svg> Kits</a></li>
                    <li><a href="/admin/kits/manuals/index.php" class="<?= strpos($_SERVER['PHP_SELF'], '/kits/manuals/') !== false ? 'active' : '' ?>"><svg class="admin-icon" width="16" height="16" aria-hidden="true"><use xlink:href="#icon-article"/></svg> Manuales</a></li>
                    <li><a href="/admin/componentes/index.php" class="<?= strpos($_SERVER['PHP_SELF'], '/componentes/') !== false ? 'active' : '' ?>"><svg class="admin-icon" width="16" height="16" aria-hidden="true"><use xlink:href="#icon-flask"/></svg> Componentes</a></li>
                    <li><a href="/admin/contratos/index.php" class="<?= strpos($_SERVER['PHP_SELF'], '/contratos/') !== false ? 'active' : '' ?>"><svg class="admin-icon" width="16" height="16" aria-hidden="true"><use xlink:href="#icon-folder"/></svg> Contratos</a></li>
                    <li><a href="/admin/entregas/index.php" class="<?= strpos($_SERVER['PHP_SELF'], '/entregas/') !== false ? 'active' : '' ?>"><svg class="admin-icon" width="16" height="16" aria-hidden="true"><use xlink:href="#icon-calendar"/></svg> Entregas</a></li>
                    <li><a href="/admin/lotes/index.php" class="<?= strpos($_SERVER['PHP_SELF'], '/lotes/') !== false ? 'active' : '' ?>"><svg class="admin-icon" width="16" height="16" aria-hidden="true"><use xlink:href="#icon-folder"/></svg> Lotes</a></li>
                    <li><a href="/admin/ia/index.php" class="<?= strpos($_SERVER['PHP_SELF'], '/ia/') !== false ? 'active' : '' ?>"><svg class="admin-icon" width="16" height="16" aria-hidden="true"><use xlink:href="#icon-chart"/></svg> IA</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="admin-main">
                        <script>
                        (function(){
                            const btn = document.getElementById('btnRefreshSearch');
                            if (!btn) return;
                            btn.addEventListener('click', async function(){
                                btn.disabled = true;
                                const prevText = btn.textContent;
                                btn.textContent = '⏳ Actualizando…';
                                try {
                                    const resp = await fetch('/api/search-refresh.php', { method: 'POST', headers: { 'Accept':'application/json' } });
                                    const data = await resp.json();
                                    console.log('📡 [Admin] search-refresh status:', resp.status, data);
                                    if (data && data.ok) {
                                        btn.textContent = '✅ Refrescado';
                                        setTimeout(() => { btn.textContent = prevText; btn.disabled = false; }, 1200);
                                    } else {
                                        btn.textContent = '⚠️ Error';
                                        setTimeout(() => { btn.textContent = prevText; btn.disabled = false; }, 1500);
                                    }
                                } catch (e) {
                                    console.log('❌ [Admin] search-refresh error:', e && e.message);
                                    btn.textContent = '⚠️ Error de red';
                                    setTimeout(() => { btn.textContent = prevText; btn.disabled = false; }, 1500);
                                }
                            });
                        })();
                        </script>
