// =============================================================================
// search-overlay.js — Full-screen search overlay powered by Pagefind.
// Open: click .search-trigger button or Cmd/Ctrl+K.
// Close: Escape key, backdrop click, or close button.
// =============================================================================

(function () {
    'use strict';

    var overlay       = null;
    var input         = null;
    var results       = null;
    var pagefind      = null;
    var timer         = null;
    var isLoading     = false;
    var previousFocus = null;

    function init() {
        overlay = document.getElementById('site-search');
        if (!overlay) return;

        input   = overlay.querySelector('.search-overlay__input');
        results = overlay.querySelector('.search-overlay__results');

        // Trigger buttons (any .search-trigger on the page)
        document.querySelectorAll('.search-trigger').forEach(function (btn) {
            btn.addEventListener('click', open);
        });

        // Close button
        var closeBtn = overlay.querySelector('.search-overlay__close');
        if (closeBtn) closeBtn.addEventListener('click', close);

        // Backdrop click
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) close();
        });

        // Keyboard — global shortcuts
        document.addEventListener('keydown', function (e) {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                overlay.classList.contains('is-open') ? close() : open();
            }
            if (e.key === 'Escape' && overlay.classList.contains('is-open')) {
                close();
            }
        });

        // Input handler
        input.addEventListener('input', function () {
            clearTimeout(timer);
            var q = input.value.trim();
            if (!q) { results.innerHTML = ''; return; }
            timer = setTimeout(function () { doSearch(q); }, 220);
        });

        // Arrow key navigation from input → first result
        input.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                var first = results.querySelector('.search-overlay__result');
                if (first) first.focus();
            }
        });

        // Arrow key navigation within results (event delegation)
        results.addEventListener('keydown', function (e) {
            if (e.key !== 'ArrowDown' && e.key !== 'ArrowUp') return;
            e.preventDefault();
            var items = Array.from(results.querySelectorAll('.search-overlay__result'));
            var idx   = items.indexOf(document.activeElement);
            if (e.key === 'ArrowDown') {
                if (idx < items.length - 1) items[idx + 1].focus();
            } else {
                if (idx > 0) items[idx - 1].focus();
                else input.focus();
            }
        });
    }

    function open() {
        if (!overlay) return;
        previousFocus = document.activeElement;
        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        setTimeout(function () { input.focus(); }, 50);
        loadPagefind();
    }

    function close() {
        if (!overlay) return;
        clearTimeout(timer);
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        input.value = '';
        results.innerHTML = '';
        if (previousFocus) previousFocus.focus();
    }

    function loadPagefind() {
        if (pagefind || isLoading) return;
        isLoading = true;
        import('/pagefind/pagefind.js').then(function (pf) {
            pagefind  = pf;
            isLoading = false;
            // If user already typed something, search now
            var q = input.value.trim();
            if (q) doSearch(q);
        }).catch(function () {
            isLoading = false;
        });
    }

    function doSearch(query) {
        if (!pagefind) return;
        pagefind.search(query).then(function (search) {
            var top = search.results.slice(0, 8);
            if (!top.length) {
                var zeroTpl = overlay.dataset.zeroResults || 'No results for "[SEARCH_TERM]"';
                results.innerHTML = '<p class="search-overlay__empty">' + esc(zeroTpl).replace('[SEARCH_TERM]', '<strong>' + esc(query) + '</strong>') + '</p>';
                return;
            }
            Promise.all(top.map(function (r) { return r.data(); })).then(function (items) {
                render(items);
            });
        });
    }

    function render(items) {
        results.innerHTML = items.map(function (r) {
            var title = r.meta.title || r.url;
            return '<a href="' + esc(r.url) + '" class="search-overlay__result">' +
                '<span class="search-overlay__result-title">' + esc(title) + '</span>' +
                '<span class="search-overlay__result-excerpt">' + r.excerpt + '</span>' +
            '</a>';
        }).join('');
    }

    function esc(s) {
        return String(s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    document.addEventListener('DOMContentLoaded', init);
}());
