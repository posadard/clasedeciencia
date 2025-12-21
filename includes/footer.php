    </main>
    
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Acerca de <?= SITE_NAME ?></h3>
                    <p>Plataforma de formación científica para estudiantes de educación básica y media, con guías interactivas, proyectos prácticos y orientación personalizada.</p>
                </div>
                
                <div class="footer-section">
                    <h3>Enlaces Rápidos</h3>
                    <ul>
                        <li><a href="/">Inicio</a></li>
                        <li><a href="/clases">Clases</a></li>
                        <li><a href="/contact.php">Contacto</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Información</h3>
                    <ul>
                        <li><a href="/sobre-nosotros.php">Sobre Nosotros</a></li>
                        <li><a href="/terms.php">Términos de Uso</a></li>
                        <li><a href="/privacy.php">Política de Privacidad</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Páginas de Interés</h3>
                    <ul>
                        <li><a href="https://www.unesco.org/es/education" target="_blank" rel="noopener">UNESCO - Educación</a></li>
                        <li><a href="https://www.mineducacion.gov.co/" target="_blank" rel="noopener">Ministerio de Educación (Colombia)</a></li>
                        <li><a href="https://www.ibe.unesco.org/" target="_blank" rel="noopener">IBE-UNESCO</a></li>
                        <li><a href="https://www.oas.org/es/sedi/dde/" target="_blank" rel="noopener">OEA - Educación</a></li>
                    </ul>
                </div>
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
