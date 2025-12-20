  </main>

  <!-- Scroll to Top Button -->
  <button id="scroll-to-top" class="scroll-to-top" aria-label="Scroll to top" title="Back to top">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M7 14L12 9L17 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
  </button>

  <!-- Footer -->
  <footer class="footer" role="contentinfo">
    <div class="container">
      <div class="footer-content">
        <div class="footer-section">
          <h3>Free Science Project</h3>
          <p>Providing quality science education resources since 2005. Our mission is to make STEM learning accessible, engaging, and fun for students of all ages.</p>
          <div class="social-links">
            <!-- Add social media links here when available -->
          </div>
        </div>
        
        <div class="footer-section">
          <h3>Quick Links</h3>
          <ul>
            <li><a href="/primary_projects.php">Primary Projects (K-4)</a></li>
            <li><a href="/elementary_projects.php">Elementary Projects (4-6)</a></li>
            <li><a href="/intermediate_projects.php">Intermediate Projects (7-8)</a></li>
            <li><a href="/senior_projects.php">Senior Projects (9-12)</a></li>
            <li><a href="/projects/">All Projects</a></li>
          </ul>
        </div>
        
        <div class="footer-section">
          <h3>Science Subjects</h3>
          <ul>
            <li><a href="/projects/?subject=physics">Physics Experiments</a></li>
            <li><a href="/projects/?subject=chemistry">Chemistry Projects</a></li>
            <li><a href="/projects/?subject=biology">Biology Activities</a></li>
            <li><a href="/projects/?subject=earth-science">Earth Science</a></li>
            <li><a href="/projects/?subject=engineering">Engineering Projects</a></li>
          </ul>
        </div>
        
        <div class="footer-section">
          <h3>Educational Partners</h3>
          <ul>
            <li><a href="https://www.scienceproject.com" target="_blank" rel="noopener">ScienceProject.com</a></li>
            <li><a href="https://www.kidslovekits.com" target="_blank" rel="noopener">Kids Love Kits</a></li>
            <li><a href="http://store.SchoolOrders.com" target="_blank" rel="noopener">School Orders</a></li>
            <li><a href="http://shop.MiniScience.com" target="_blank" rel="noopener">Mini Science</a></li>
          </ul>
        </div>
      </div>
      
      <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> Free Science Project. All rights reserved. | 
        <a href="/privacy-policy">Privacy Policy</a> | 
        <a href="/terms-of-service">Terms of Service</a> | 
        <a href="/contact">Contact Us</a></p>
        
        <div class="disclaimer">
          <p><strong>Safety Disclaimer:</strong> Although most experiments are regarded as low hazard, we expressly disclaim all liabilities for any occurrence, including damage, injury or death which might arise as a consequence of using any information described here. Use all information at your own risk and always follow proper safety procedures.</p>
        </div>
      </div>
    </div>
  </footer>

  <!-- JavaScript -->
  <script src="/js/seo-geo-optimization.js?v=<?php echo filemtime(__DIR__ . '/../js/seo-geo-optimization.js'); ?>" defer></script>
  <script src="/js/interactive-features.js?v=<?php echo filemtime(__DIR__ . '/../js/interactive-features.js'); ?>" defer></script>
  
  <!-- Cookie Consent (for GDPR compliance) -->
  <script>
    window.addEventListener('load', function() {
      // Simple cookie consent implementation
      if (!localStorage.getItem('cookieConsent')) {
        const banner = document.createElement('div');
        banner.innerHTML = `
          <div style="position: fixed; bottom: 0; left: 0; right: 0; background: var(--text-dark); color: white; padding: 1rem; z-index: 1000; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <span>We use cookies to improve your browsing experience and analyze site traffic.</span>
            <button onclick="acceptCookies()" style="background: var(--primary-color); color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; white-space: nowrap;">Accept</button>
          </div>
        `;
        document.body.appendChild(banner);
      }
    });
    
    function acceptCookies() {
      localStorage.setItem('cookieConsent', 'true');
      const banner = document.querySelector('[style*="position: fixed"][style*="bottom: 0"]');
      if (banner) banner.remove();
    }
  </script>
  
  <!-- Scroll to Top Functionality -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const scrollToTopButton = document.getElementById('scroll-to-top');
      
      // Show/hide button based on scroll position
      window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
          scrollToTopButton.classList.add('visible');
        } else {
          scrollToTopButton.classList.remove('visible');
        }
      });
      
      // Smooth scroll to top when button is clicked
      scrollToTopButton.addEventListener('click', function() {
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
        
        // Analytics tracking
        if (typeof gtag !== 'undefined') {
          gtag('event', 'scroll_to_top', {
            event_category: 'engagement',
            event_label: 'scroll_button'
          });
        }
      });
    });
  </script>
</body>

</html>