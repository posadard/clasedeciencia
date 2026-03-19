    </main>
<?php
// ── Footer dinámico: carga grupos y enlaces desde BD ──────────────────────────
$_footer_grupos = [];
$_footer_texto_sobre = 'Plataforma de formación científica para estudiantes de educación básica y media, con guías interactivas, proyectos prácticos y orientación personalizada.';
try {
    // $pdo está disponible porque config.php fue incluido en cada página pública
    $_footer_grupos = cdc_get_footer_data($pdo);
    $_footer_texto_sobre = cdc_get_sitio_config(
        $pdo,
        'footer_texto_sobre',
        $_footer_texto_sobre
    );
} catch (Throwable $_fe) {
    error_log('footer.php DB error: ' . $_fe->getMessage());
    // Fallback: footer sin columnas dinámicas — no genera error visible al usuario
}
?>
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <!-- Columna 1: Acerca de (texto desde sitio_config) -->
                <div class="footer-section">
                    <h3>Acerca de <?= SITE_NAME ?></h3>
                    <p><?= h($_footer_texto_sobre) ?></p>
                </div>

                <!-- Columnas dinámicas desde footer_grupos + footer_enlaces -->
                <?php foreach ($_footer_grupos as $_fgrupo): ?>
                <div class="footer-section">
                    <h3><?= h($_fgrupo['titulo']) ?></h3>
                    <ul>
                        <?php foreach ($_fgrupo['enlaces'] as $_fenlace): ?>
                        <li>
                            <a href="<?= h($_fenlace['url']) ?>"
                               <?= $_fenlace['externo'] ? 'target="_blank" rel="noopener"' : '' ?>>
                                <?= h($_fenlace['etiqueta']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. Todos los derechos reservados.</p>
                <p class="print-only">Impreso desde <?= SITE_URL ?> el <?= date('d/m/Y') ?></p>
            </div>
        </div>
    </footer>
    
    <script src="/assets/js/main.js"></script>
    <!-- Search System: cargar global para que la caja del header funcione en todo el sitio -->
    <script src="/assets/js/home-search.js"></script>
</body>
</html>
