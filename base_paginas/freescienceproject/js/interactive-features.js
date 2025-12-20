// Enhanced User Experience and Interactivity
document.addEventListener('DOMContentLoaded', function() {
    initializeFAQ();
    // Only initialize search on specific pages, not homepage
    if (!document.body.classList.contains('homepage') && window.location.pathname !== '/') {
        initializeSearch();
    }
    initializeLazyLoading();
    initializePerformanceOptimizations();
    initializeAccessibility();
});

// FAQ Accordion functionality
function initializeFAQ() {
    const faqQuestions = document.querySelectorAll('.faq-question');
    
    faqQuestions.forEach(question => {
        question.addEventListener('click', function() {
            const faqItem = this.parentElement;
            const isActive = faqItem.classList.contains('active');
            
            // Close all FAQ items
            document.querySelectorAll('.faq-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Open clicked item if it wasn't already active
            if (!isActive) {
                faqItem.classList.add('active');
            }
            
            // Track FAQ interaction for analytics
            if (typeof gtag !== 'undefined') {
                gtag('event', 'faq_interaction', {
                    faq_question: this.textContent.trim(),
                    action: isActive ? 'close' : 'open'
                });
            }
        });
        
        // Add keyboard accessibility
        question.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
        
        // Make focusable for accessibility
        question.setAttribute('tabindex', '0');
        question.setAttribute('role', 'button');
        question.setAttribute('aria-expanded', 'false');
        
        const faqItem = question.parentElement;
        faqItem.addEventListener('transitionend', function() {
            const isActive = this.classList.contains('active');
            question.setAttribute('aria-expanded', isActive ? 'true' : 'false');
        });
    });
}

// Enhanced search functionality
function initializeSearch() {
    const searchInput = document.querySelector('.search-input');
    
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length > 2) {
                searchTimeout = setTimeout(() => {
                    performSearch(query);
                }, 300); // Debounce search
            }
        });
        
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = this.value.trim();
                if (query) {
                    performSearch(query);
                }
            }
        });
    }
}

// Perform search functionality
function performSearch(query) {
    // Track search for analytics
    if (typeof gtag !== 'undefined') {
        gtag('event', 'search', {
            search_term: query
        });
    }
    
    // Simple client-side search through project cards
    const projectCards = document.querySelectorAll('.project-card');
    let visibleResults = 0;
    
    projectCards.forEach(card => {
        const title = card.querySelector('h3')?.textContent.toLowerCase() || '';
        const description = card.querySelector('p')?.textContent.toLowerCase() || '';
        const searchText = (title + ' ' + description).toLowerCase();
        
        if (searchText.includes(query.toLowerCase())) {
            card.style.display = 'block';
            highlightSearchTerms(card, query);
            visibleResults++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Note: Search results info removed - using dropdown search instead
}

// Highlight search terms in results
function highlightSearchTerms(element, query) {
    const textElements = element.querySelectorAll('h3, p');
    const regex = new RegExp(`(${query})`, 'gi');
    
    textElements.forEach(textEl => {
        const originalText = textEl.textContent;
        const highlightedText = originalText.replace(regex, '<mark>$1</mark>');
        if (highlightedText !== originalText) {
            textEl.innerHTML = highlightedText;
        }
    });
}

// Lazy loading for images
function initializeLazyLoading() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
}

// Performance optimizations
function initializePerformanceOptimizations() {
    // Preload critical resources
    preloadCriticalResources();
    
    // Optimize animations based on user preferences
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.documentElement.style.setProperty('--transition', 'none');
    }
    
    // Add smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Preload critical resources
function preloadCriticalResources() {
    // Keep the preload list tiny. Only preload the true hero image site-wide.
    // Page-specific critical assets can be added conditionally to avoid
    // unnecessary early network pressure on unrelated pages.
    const siteHero = '/images/freescienceproject02.jpg';
    const pageSpecific = '/images/air_battery_1.jpg'; // used only on airbattery project

    const addLinkOnce = (rel, as, href) => {
        try {
            // Avoid adding duplicates
            if (document.querySelector(`link[rel="${rel}"][href="${href}"]`)) return;
            const link = document.createElement('link');
            link.rel = rel;
            if (as) link.as = as;
            link.href = href;
            // Use low-risk append; some environments may block preloads, so wrap
            document.head.appendChild(link);
        } catch (e) {
            // Fail silently; avoid breaking page JS on older browsers
            console && console.debug && console.debug('preload/prefetch skipped', href, e);
        }
    };

    // Site-wide hero is worth preloading for perceived performance
    addLinkOnce('preload', 'image', siteHero);

    // If we're on the airbattery project page, preload its hero as well.
    // Use pathname checks that work with both /projects/airbattery and /projects/airbattery/index.php
    const path = (window.location && window.location.pathname) || '';
    if (path.indexOf('/projects/airbattery') !== -1 || path.indexOf('/airbattery') !== -1) {
        // Use prefetch for the project hero to reduce unused-preload warnings; site hero remains preloaded.
        addLinkOnce('prefetch', 'image', pageSpecific);
    } else {
        // For other pages, prefetch other useful but non-critical assets instead of preloading
        addLinkOnce('prefetch', 'image', '/images/DNA_model5.jpg');
    }
}

// Accessibility enhancements
function initializeAccessibility() {
    // Enhance focus management
    enhanceFocusManagement();
    
    // Add ARIA labels where needed
    addARIALabels();
    
    // Keyboard navigation for cards
    initializeCardKeyboardNavigation();
}

// Enhance focus management
function enhanceFocusManagement() {
    // Add focus indicators for keyboard users
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            document.body.classList.add('keyboard-navigation');
        }
    });
    
    document.addEventListener('mousedown', function() {
        document.body.classList.remove('keyboard-navigation');
    });
}

// Add ARIA labels for better screen reader support
function addARIALabels() {
    // Add labels to project cards
    document.querySelectorAll('.project-card').forEach((card, index) => {
        card.setAttribute('role', 'article');
        card.setAttribute('aria-labelledby', `project-title-${index}`);
        
        const title = card.querySelector('h3');
        if (title) {
            title.id = `project-title-${index}`;
        }
    });
    
    // Add labels to grade level cards
    document.querySelectorAll('.grade-level-card').forEach((card, index) => {
        card.setAttribute('role', 'region');
        card.setAttribute('aria-labelledby', `grade-title-${index}`);
        
        const title = card.querySelector('h3');
        if (title) {
            title.id = `grade-title-${index}`;
        }
    });
}

// Keyboard navigation for project cards
function initializeCardKeyboardNavigation() {
    const cards = document.querySelectorAll('.project-card, .grade-level-card');
    
    cards.forEach(card => {
        card.setAttribute('tabindex', '0');
        
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const link = this.querySelector('a');
                if (link) {
                    link.click();
                }
            }
        });
    });
}

// Error handling and user feedback
window.addEventListener('error', function(e) {
    console.error('JavaScript error:', e.error);
    
    // Track errors for analytics (but don't expose sensitive info)
    if (typeof gtag !== 'undefined') {
        gtag('event', 'exception', {
            description: 'JavaScript error occurred',
            fatal: false
        });
    }
});

// Service Worker registration for offline support (if available)
// Service Worker registration for offline support (if available)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        // Check whether /sw.js exists before attempting to register to avoid 404 noise in logs
        const controllerUrl = '/sw.js';
        const controllerCheck = new Promise((resolve) => {
            // Use a fast HEAD request if supported; fallback to GET
            const controllerTimeout = setTimeout(() => resolve(false), 1500);
            fetch(controllerUrl, { method: 'HEAD', cache: 'no-store' }).then(resp => {
                clearTimeout(controllerTimeout);
                resolve(resp && resp.ok);
            }).catch(() => {
                // If HEAD fails (some hosts block it), try a lightweight GET
                fetch(controllerUrl, { method: 'GET', cache: 'no-store' }).then(r => {
                    clearTimeout(controllerTimeout);
                    resolve(r && r.ok);
                }).catch(() => {
                    clearTimeout(controllerTimeout);
                    resolve(false);
                });
            });
        });

        controllerCheck.then(exists => {
            if (!exists) {
                // No service worker present on this host/environment; skip registration silently
                console && console.info && console.info('No service worker found; skipping registration');
                return;
            }

            navigator.serviceWorker.register(controllerUrl)
                .then(function(registration) {
                    console.log('ServiceWorker registered successfully');
                })
                .catch(function(error) {
                    console.log('ServiceWorker registration failed', error);
                });
        });
    });
}

// Progressive enhancement for modern browsers
function initializeModernFeatures() {
    // Intersection Observer for scroll animations
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                }
            });
        }, { threshold: 0.1 });
        
        document.querySelectorAll('.project-card, .grade-level-card').forEach(el => {
            observer.observe(el);
        });
    }
    
    // Share functionality removed per user request
}

// Initialize modern features when DOM is ready
document.addEventListener('DOMContentLoaded', initializeModernFeatures);

// Export functions for potential external use
window.FreeScienceProject = {
    search: performSearch
};