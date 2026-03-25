        </main>
    </div>

    <?php
    $admin_path = (string)($_SERVER['PHP_SELF'] ?? '');
    $contexto_pagina = 'admin';
    if (strpos($admin_path, '/admin/contratos/') !== false) {
        $contexto_pagina = 'contratos';
    } elseif (strpos($admin_path, '/admin/entregas/') !== false) {
        $contexto_pagina = 'entregas';
    } elseif (strpos($admin_path, '/admin/lotes/') !== false) {
        $contexto_pagina = 'lotes';
    } elseif (strpos($admin_path, '/admin/clases/') !== false) {
        $contexto_pagina = 'clases';
    } elseif (strpos($admin_path, '/admin/kits/') !== false) {
        $contexto_pagina = 'kits';
    } elseif (strpos($admin_path, '/admin/componentes/') !== false) {
        $contexto_pagina = 'componentes';
    } elseif (strpos($admin_path, '/admin/paginas/') !== false) {
        $contexto_pagina = 'paginas';
    } elseif (strpos($admin_path, '/admin/footer/') !== false) {
        $contexto_pagina = 'footer';
    } elseif (strpos($admin_path, '/admin/sitio/') !== false) {
        $contexto_pagina = 'sitio';
    } elseif (strpos($admin_path, '/admin/ia/') !== false) {
        $contexto_pagina = 'ia';
    } elseif (strpos($admin_path, '/admin/dashboard.php') !== false) {
        $contexto_pagina = 'admin';
    }

    $entidad_tipo = '';
    if (in_array($contexto_pagina, ['contratos', 'entregas', 'lotes'], true)) {
        $entidad_tipo = rtrim($contexto_pagina, 's');
    }
    $entidad_id = (isset($_GET['edit']) && ctype_digit((string)$_GET['edit'])) ? (int)$_GET['edit'] : 0;
    ?>
    <script src="/assets/js/asistente-ia.js"></script>
    <script>
    (function(){
      if (!window.initAsistenteIA) {
        console.log('❌ [Admin IA] initAsistenteIA no disponible');
        return;
      }
      window.initAsistenteIA({
        instancia: 'backend',
                contextoScope: 'admin_global',
        pagina: '<?= htmlspecialchars($contexto_pagina, ENT_QUOTES, 'UTF-8') ?>',
        contextoPagina: '<?= htmlspecialchars($contexto_pagina, ENT_QUOTES, 'UTF-8') ?>',
        entidadTipo: '<?= htmlspecialchars($entidad_tipo, ENT_QUOTES, 'UTF-8') ?>',
        entidadId: <?= (int)$entidad_id ?>
      });
      console.log('✅ [Admin IA] Panel lateral inicializado en contexto:', '<?= htmlspecialchars($contexto_pagina, ENT_QUOTES, 'UTF-8') ?>');
    })();
    </script>
</body>
</html>
