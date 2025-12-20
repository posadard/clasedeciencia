    </main>
    
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About <?= SITE_NAME ?></h3>
                    <p>Practical chemistry guidance for homesteaders and farmers. Simple, safe methods using common chemicals.</p>
                </div>
                
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="/">Home</a></li>
                        <li><a href="/library.php">Library</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Information</h3>
                    <ul>
                        <li><a href="/about.php">About Us</a></li>
                        <li><a href="/contact.php">Contact</a></li>
                        <li><a href="/terms.php">Terms of Use</a></li>
                        <li><a href="/privacy.php">Privacy Policy</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Partner Sites</h3>
                    <ul>
                        <li><a href="<?= h(CHEMICALSTORE_URL) ?>" target="_blank" rel="noopener">ChemicalStore.com</a></li>
                        <li><a href="<?= h(SDS_URL) ?>" target="_blank" rel="noopener">Safety Data Sheets</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved. | A ChemicalStore.com Publication</p>
                <p class="print-only">Printed from <?= SITE_URL ?> on <?= date('F j, Y') ?></p>
            </div>
        </div>
    </footer>
    
    <script src="/assets/js/main.js"></script>
</body>
</html>
