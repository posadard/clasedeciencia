/**
 * Individual Project Page JavaScript
 * Handles Related Supplies functionality specifically for individual project pages
 * Only executes on /projects/[project]/ pages
 */

(function() {
    'use strict';
    
    // Only run on individual project pages
    if (!window.location.pathname.includes('/projects/') || 
        window.location.pathname === '/projects/' || 
        window.location.pathname.endsWith('/projects/index.php')) {
        console.log('individual-project-page.js: Not an individual project page, skipping');
        return;
    }
    
    console.log('individual-project-page.js: Initializing for individual project page');
    
    function makeSuppliesClickable() {
        // Find all AbanteCart category items
        const supplyItems = document.querySelectorAll('.abantecart_category:not([data-individual-click-handled])');
        console.log('Found supply items on individual page:', supplyItems.length);
        
        supplyItems.forEach(item => {
            // Mark as handled
            item.setAttribute('data-individual-click-handled', 'true');
            item.style.cursor = 'pointer';
            
            // Add click handler to the entire li element
            item.addEventListener('click', function(e) {
                // Find the link with data-href
                const link = this.querySelector('a[data-href]');
                if (link && link.dataset.href) {
                    // Prevent modal from opening
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const url = link.dataset.href;
                    console.log('Opening supply category in new tab:', url);
                    window.open(url, '_blank');
                } else {
                    // Fallback: construct URL from data-category-id
                    const categoryId = this.dataset.categoryId;
                    if (categoryId) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        const fallbackUrl = `https://shop.miniscience.com/index.php?rt=product/category&category_id=${categoryId}`;
                        console.log('Opening supply category in new tab (fallback):', fallbackUrl);
                        window.open(fallbackUrl, '_blank');
                    }
                }
            });
            
            // Override the existing modal links to prevent them from opening modals
            const modalLinks = item.querySelectorAll('a[data-toggle="abcmodal"]');
            modalLinks.forEach(modalLink => {
                modalLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Let the parent li handle the click
                    item.click();
                });
            });
            
            // Add hover effects
            item.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f0f3ff';
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.1)';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
                this.style.transform = '';
                this.style.boxShadow = '';
            });
        });
        
        if (supplyItems.length > 0) {
            console.log(`Made ${supplyItems.length} supply items clickable on individual project page`);
        }
    }
    
    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', makeSuppliesClickable);
    } else {
        makeSuppliesClickable();
    }
    
    // Watch for dynamically added content (AbanteCart loads via JavaScript)
    const observer = new MutationObserver(function(mutations) {
        let hasNewSupplies = false;
        
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        const newSupplies = node.querySelectorAll ? 
                            node.querySelectorAll('.abantecart_category:not([data-individual-click-handled])') : [];
                        if (newSupplies.length > 0) {
                            hasNewSupplies = true;
                        }
                    }
                });
            }
        });
        
        if (hasNewSupplies) {
            console.log('individual-project-page.js: New supplies detected, making them clickable');
            setTimeout(makeSuppliesClickable, 100);
        }
    });
    
    // Start observing
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    // Also run periodically to catch any missed items
    setTimeout(makeSuppliesClickable, 1000);
    setTimeout(makeSuppliesClickable, 3000);
    
})();