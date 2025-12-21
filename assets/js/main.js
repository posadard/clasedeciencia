/**
 * The Green Almanac - Main JavaScript
 * Minimal JS for progressive enhancement
 */

(function() {
    'use strict';
    
    // Simple form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'red';
                } else {
                    field.style.borderColor = '';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });
    
    // Print button functionality
    const printButtons = document.querySelectorAll('.print-button');
    printButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            window.print();
        });
    });
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Make cards clickable (delegated): clicking anywhere on a card follows its primary link
    // - Uses event delegation so it works for dynamic content
    // - Respects real interactive elements (anchors, buttons, inputs) so their default behavior stays
    // - Prefers a `data-href` on the card, otherwise finds the first inner anchor with an href
    // - Adds keyboard activation (Enter/Space) and pointer affordance
    (function() {
        const cardSelectors = [
            '.article-card',
            '.material-card',
            '.material-item',
            '.product-embed-wrapper',
            '.card'
        ];
        const selector = cardSelectors.join(',');

        // Add pointer affordance and make focusable where appropriate
        function enhanceExistingCards() {
            document.querySelectorAll(selector).forEach(card => {
                // Only set pointer/tabIndex if element is not inherently interactive
                card.classList.add('is-clickable');
                if (!card.hasAttribute('tabindex')) card.setAttribute('tabindex', '0');
            });
        }

        // Resolve primary link for a card
        function resolveCardHref(card) {
            if (!card) return null;
            const dataHref = card.getAttribute('data-href');
            if (dataHref) return { href: dataHref, target: null };
            const link = card.querySelector('a[href]');
            if (link) return { href: link.href, target: link.target || null };
            return null;
        }

        // Ignore clicks that originate from interactive elements that should handle the event
        function clickedInteractiveInsideCard(e, card) {
            // If the click is inside an anchor, let the anchor handle it
            const anchor = e.target.closest('a');
            if (anchor && card.contains(anchor)) return true;
            // Buttons, inputs, labels, selects, textareas, and summary elements should be ignored
            const control = e.target.closest('button, input, textarea, select, label, summary');
            if (control && card.contains(control)) return true;
            return false;
        }

        // Handle click delegation
        document.addEventListener('click', function(e) {
            const card = e.target.closest(selector);
            if (!card) return; // not a card

            if (clickedInteractiveInsideCard(e, card)) return; // let the inner control act

            const resolved = resolveCardHref(card);
            if (!resolved) return;

            e.preventDefault();
            if (resolved.target === '_blank') {
                window.open(resolved.href, '_blank');
            } else {
                window.location.href = resolved.href;
            }
        }, false);

        // Keyboard activation: Enter or Space should follow the card link
        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Enter' && e.key !== ' ') return;
            const card = document.activeElement && document.activeElement.matches && document.activeElement.matches(selector)
                ? document.activeElement
                : null;
            if (!card) return;

            const resolved = resolveCardHref(card);
            if (!resolved) return;

            e.preventDefault();
            if (resolved.target === '_blank') {
                window.open(resolved.href, '_blank');
            } else {
                window.location.href = resolved.href;
            }
        }, false);

        // Initial enhancement pass
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', enhanceExistingCards);
        } else {
            enhanceExistingCards();
        }
    })();
    
})();

/* Material tracking: expose a global function for inline onclicks and track widget clicks */
(function() {
    // Global function used by markup: onclick="trackMaterialClick(this)"
    window.trackMaterialClick = function(element) {
        try {
            const materialId = element.getAttribute('data-material-id');
            const clickType = element.getAttribute('data-click-type') || 'purchase_link';
            if (!materialId) return true;
            const articleId = window.__ARTICLE_ID || '';
            const body = new URLSearchParams({
                material_id: materialId,
                click_type: clickType,
                source_page: '/article.php',
                source_article_id: articleId
            });

            fetch('/api/track-click.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            }).then(r => {
                if (!r.ok) console.warn('[track-click] response not ok', r.status);
                return r.json().catch(() => null);
            }).then(j => {
                if (j) console.log('[track-click] result', j);
            }).catch(err => console.warn('[track-click] failed', err));
        } catch (err) {
            // Fail silently
            console.error(err);
        }
        return true;
    };

        // We no longer track clicks inside embedded widgets (widget_click removed).
})();

// Gentle leaf flutter effect in hero (decorative, low motion)
(function() {
    'use strict';
    function initLeafFlutter() {
        const overlay = document.querySelector('.leaf-overlay');
        if (!overlay) return;
        const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduceMotion) {
            console.log('⚠️ [leaves] Motion disabled by user preference');
            return;
        }

        console.log('🔍 [leaves] Init overlay');
        const maxLeavesDesktop = 10;
        const maxLeavesMobile = 5;
        const isMobile = window.matchMedia && window.matchMedia('(max-width: 768px)').matches;
        const maxConcurrent = isMobile ? maxLeavesMobile : maxLeavesDesktop;

        let activeCount = 0;

        function random(min, max) { return Math.random() * (max - min) + min; }
        function randomInt(min, max) { return Math.floor(random(min, max)); }

        function createLeafSVG(color) {
            const SVG_NS = 'http://www.w3.org/2000/svg';
            const svg = document.createElementNS(SVG_NS, 'svg');
            svg.setAttribute('viewBox', '0 0 24 24');
            svg.setAttribute('aria-hidden', 'true');
            // Simple leaf path (stylized)
            const path = document.createElementNS(SVG_NS, 'path');
            path.setAttribute('d', 'M12 2 C8 4,5 8,6 12 C7 16,11 20,15 18 C19 16,22 10,18 6 C16 4,14 3,12 2 Z');
            path.setAttribute('fill', color);
            path.setAttribute('opacity', '0.85');
            svg.appendChild(path);
            return svg;
        }

        function spawnLeaf() {
            if (activeCount >= maxConcurrent) return;
            activeCount++;
            const directionRev = Math.random() < 0.5;
            const topPct = random(5, 95).toFixed(2) + '%';
            const size = randomInt(18, 30) + 'px';
            const duration = random(10, 18).toFixed(2) + 's';
            const delay = random(0, 2).toFixed(2) + 's';
            const spinDeg = (directionRev ? random(-120, -60) : random(60, 120)).toFixed(1) + 'deg';

            // Use existing palette variables if available, otherwise fallback
            const leafColors = ['#a5d6a7', '#c8e6c9', '#e8f5e9'];
            const color = leafColors[randomInt(0, leafColors.length)];

            const wrapper = document.createElement('div');
            wrapper.className = 'leaf' + (directionRev ? ' rev' : '');
            wrapper.style.setProperty('--top', topPct);
            wrapper.style.setProperty('--size', size);
            wrapper.style.setProperty('--duration', duration);
            wrapper.style.setProperty('--delay', delay);
            wrapper.style.setProperty('--spin', spinDeg);

            const svg = createLeafSVG(color);
            wrapper.appendChild(svg);
            overlay.appendChild(wrapper);

            function cleanup() {
                if (wrapper && wrapper.parentNode) wrapper.parentNode.removeChild(wrapper);
                activeCount = Math.max(0, activeCount - 1);
                console.log('✅ [leaves] Leaf completed');
            }
            wrapper.addEventListener('animationend', cleanup, { once: true });
        }

        function spawnWave() {
            const count = randomInt(1, 3);
            console.log('🔍 [leaves] Wave spawn:', count);
            for (let i = 0; i < count; i++) {
                setTimeout(spawnLeaf, i * randomInt(250, 600));
            }
        }

        // initial leaves
        spawnWave();
        // occasional waves
        setInterval(spawnWave, randomInt(12000, 22000));
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLeafFlutter);
    } else {
        initLeafFlutter();
    }
})();
