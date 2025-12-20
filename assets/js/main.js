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

/* Enhanced Global Search Client (spinner, cache, highlight, badges) */
(function() {
    function initGlobalSearch() {
        const form = document.getElementById('global-search-form');
        if (!form) return;

        const input = document.getElementById('global-search-input');
        const resultsBox = document.getElementById('global-search-results');
        const spinner = document.getElementById('global-search-spinner');
        let results = [];
        let focused = -1;
        let abortCtrl = null;

        const cache = new Map(); // simple LRU capped manually
        const CACHE_LIMIT = 40;

        function escapeHtml(s) {
            return String(s || '').replace(/[&<>\"]/g, function (c) {
                return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];
            });
        }

        function highlightMatch(text, q) {
            if (!q) return escapeHtml(text);
            try {
                const safe = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                const re = new RegExp('(' + safe + ')', 'ig');
                return escapeHtml(text).replace(re, '<span class="search-highlight">$1</span>');
            } catch (e) {
                return escapeHtml(text);
            }
        }

        function render() {
            resultsBox.innerHTML = '';
            if (!results.length) {
                resultsBox.setAttribute('aria-hidden', 'true');
                resultsBox.setAttribute('aria-expanded', 'false');
                return;
            }
            resultsBox.setAttribute('aria-hidden', 'false');
            resultsBox.setAttribute('aria-expanded', 'true');

            results.forEach((item, idx) => {
                const row = document.createElement('div');
                row.className = 'search-result-item';
                row.setAttribute('role', 'option');
                row.setAttribute('data-index', idx);

                if (idx === focused) row.setAttribute('aria-selected', 'true');

                const a = document.createElement('a');
                a.href = item.url;
                a.innerHTML = `<span class="result-badge">${escapeHtml(item.type)}</span><div class="result-main"><strong>${highlightMatch(item.title, input.value)}</strong><div class="search-result-excerpt">${highlightMatch(item.excerpt||'', input.value)}</div></div>`;

                row.appendChild(a);
                row.addEventListener('mouseenter', () => { focused = idx; updateSelection(); });
                row.addEventListener('mouseleave', () => { focused = -1; updateSelection(); });

                resultsBox.appendChild(row);
            });
        }

        function updateSelection() {
            const items = resultsBox.querySelectorAll('.search-result-item');
            items.forEach(it => it.removeAttribute('aria-selected'));
            if (focused >= 0 && items[focused]) items[focused].setAttribute('aria-selected', 'true');
        }

        function clear() {
            results = [];
            focused = -1;
            render();
        }

        function showSpinner(on) {
            if (!spinner) return;
            spinner.setAttribute('aria-hidden', on ? 'false' : 'true');
        }

        let timer = null;
        function doSearch(q) {
            if (abortCtrl) { abortCtrl.abort(); abortCtrl = null; }
            if (!q || q.length < 2) { clear(); return; }

            if (cache.has(q)) {
                results = cache.get(q);
                render();
                return;
            }

            abortCtrl = new AbortController();
            showSpinner(true);

            fetch('/api/search.php?q=' + encodeURIComponent(q), { signal: abortCtrl.signal })
                .then(r => r.json())
                .then(data => {
                    results = data || [];
                    render();
                    // cache
                    try {
                        cache.set(q, results);
                        if (cache.size > CACHE_LIMIT) cache.delete(cache.keys().next().value);
                    } catch (e) {}
                }).catch(err => {
                    if (err.name === 'AbortError') return;
                    console.error('search error', err);
                }).finally(() => { showSpinner(false); abortCtrl = null; });
        }

        input.addEventListener('input', function() {
            const q = this.value.trim();
            if (timer) clearTimeout(timer);
            timer = setTimeout(() => doSearch(q), 180);
        });

        input.addEventListener('keydown', function(e) {
            const count = results.length;
            if (e.key === 'ArrowDown') {
                if (count) { focused = (focused + 1) % count; updateSelection(); scrollIntoView(); }
                e.preventDefault();
            } else if (e.key === 'ArrowUp') {
                if (count) { focused = (focused - 1 + count) % count; updateSelection(); scrollIntoView(); }
                e.preventDefault();
            } else if (e.key === 'Enter') {
                if (focused >= 0 && results[focused]) {
                    window.location.href = results[focused].url; e.preventDefault();
                }
            } else if (e.key === 'Escape') {
                clear(); input.blur();
            }
        });

        function scrollIntoView() {
            const el = resultsBox.querySelector('.search-result-item[aria-selected="true"]');
            if (el) el.scrollIntoView({ block: 'nearest' });
        }

        document.addEventListener('click', function(e) {
            if (!form.contains(e.target)) clear();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGlobalSearch);
    } else {
        initGlobalSearch();
    }
})();
