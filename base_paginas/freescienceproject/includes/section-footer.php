<?php
/**
 * Section Footer for Project Pages
 * Fixed footer component for primary_projects.php, elementary_projects.php,
 * intermediate_projects.php, and senior_projects.php
 * 
 * This footer is designed to be fixed at the bottom of the viewport,
 * with content scrolling between the fixed header and footer.
 */
?>

<!-- Scroll to Top Button -->
<button id="scroll-to-top" class="scroll-to-top" aria-label="Scroll to top" title="Back to top">
  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M7 14L12 9L17 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
</button>

<!-- Section Footer - Fixed Bottom -->
<footer class="section-footer" id="section-footer">
  <!-- Simplified Single Line Footer -->
  <div class="footer-legal">
    <div class="legal-text">
      <span class="copyright">&copy; 2025 Free Science Project. All rights reserved.</span>
      <span class="legal-separator">|</span>
      <a href="/privacy-policy" class="legal-link">Privacy Policy</a>
      <span class="legal-separator">|</span>
      <a href="/terms-of-service" class="legal-link">Terms of Service</a>
      <span class="legal-separator">|</span>
      <a href="/contact" class="legal-link">Contact Us</a>
    </div>
    <button class="disclaimer-toggle" onclick="toggleDisclaimer()" aria-label="Show Safety Disclaimer">
      <span class="disclaimer-icon">⚠️</span>
      <span class="disclaimer-text">Safety Info</span>
    </button>
  </div>
  
  <!-- Safety Disclaimer (Expandable) -->
  <div class="footer-disclaimer" id="footer-disclaimer">
    <div class="disclaimer-content">
      <strong>Safety Disclaimer:</strong> Although most experiments are regarded as low hazard, we expressly disclaim all liabilities for any occurrence, including damage, injury or death which might arise as a consequence of using any information described here. Use all information at your own risk and always follow proper safety procedures.
    </div>
  </div>
  
  <!-- Scroll Progress Indicator -->
  <div class="scroll-progress" id="scroll-progress"></div>
</footer>

<!-- Section Footer JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize scroll progress indicator
    initScrollProgress();
    
    // Initialize scroll to top button
    initScrollToTop();
});

function initScrollProgress() {
    const progressBar = document.getElementById('scroll-progress');
    if (!progressBar) return;
    
    function updateScrollProgress() {
        const scrollableHeight = document.documentElement.scrollHeight - window.innerHeight;
        const scrolled = window.scrollY;
        const progress = (scrolled / scrollableHeight) * 100;
        
        progressBar.style.width = Math.min(progress, 100) + '%';
    }
    
    // Update on scroll
    window.addEventListener('scroll', updateScrollProgress, { passive: true });
    
    // Initial update
    updateScrollProgress();
}

function initScrollToTop() {
    const scrollToTopButton = document.getElementById('scroll-to-top');
    if (!scrollToTopButton) return;
    
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
                event_label: 'scroll_button',
                source_page: window.location.pathname
            });
        }
    });
}

// Toggle disclaimer functionality
function toggleDisclaimer() {
    const disclaimer = document.getElementById('footer-disclaimer');
    const footer = document.getElementById('section-footer');
    const toggle = document.querySelector('.disclaimer-toggle');
    
    if (disclaimer.classList.contains('expanded')) {
        disclaimer.classList.remove('expanded');
        footer.classList.remove('disclaimer-open');
        toggle.setAttribute('aria-label', 'Show Safety Disclaimer');
    } else {
        disclaimer.classList.add('expanded');
        footer.classList.add('disclaimer-open');
        toggle.setAttribute('aria-label', 'Hide Safety Disclaimer');
        
        // Analytics tracking
        if (typeof gtag !== 'undefined') {
            gtag('event', 'disclaimer_viewed', {
                event_category: 'engagement',
                event_label: 'safety_disclaimer'
            });
        }
    }
}

// Add click tracking for legal links
document.querySelectorAll('.legal-link').forEach(link => {
    link.addEventListener('click', function() {
        if (typeof gtag !== 'undefined') {
            gtag('event', 'legal_link_click', {
                link_text: this.textContent.trim(),
                link_url: this.href,
                source_page: window.location.pathname
            });
        }
    });
});

// Handle cookie consent banner positioning
window.addEventListener('load', function() {
    // Adjust cookie banner position to account for fixed footer
    const adjustCookieBanner = () => {
        const cookieBanner = document.querySelector('[style*="position: fixed"][style*="bottom: 0"]');
        const footer = document.getElementById('section-footer');
        
        if (cookieBanner && cookieBanner.innerHTML.includes('cookies') && footer) {
            const footerHeight = footer.offsetHeight;
            cookieBanner.style.bottom = footerHeight + 'px'; // Position above section footer
            cookieBanner.style.zIndex = '65'; // Higher than section footer
        }
    };
    
    // Check immediately and periodically for cookie banner
    adjustCookieBanner();
    const observer = new MutationObserver(adjustCookieBanner);
    observer.observe(document.body, { childList: true });
    
    // Also adjust when disclaimer is toggled
    document.addEventListener('click', function(e) {
        if (e.target.closest('.disclaimer-toggle')) {
            setTimeout(adjustCookieBanner, 350); // Wait for animation to complete
        }
    });
});
</script>

</body>
</html>