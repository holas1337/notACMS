/**
 * Language dropdown toggle handler.
 * Tiny, self-contained — no dependencies.
 */
(function () {
    'use strict';

    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-lang-dropdown-trigger]');
        if (!trigger) {
            // Click outside → close all
            document.querySelectorAll('.lang-dropdown__menu.is-open').forEach(function (menu) {
                menu.classList.remove('is-open');
                var btn = menu.closest('[data-lang-dropdown]').querySelector('[data-lang-dropdown-trigger]');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            });
            return;
        }

        var dropdown = trigger.closest('[data-lang-dropdown]');
        var menu = dropdown.querySelector('.lang-dropdown__menu');
        var isOpen = menu.classList.contains('is-open');

        // Close others first
        document.querySelectorAll('.lang-dropdown__menu.is-open').forEach(function (m) {
            if (m !== menu) {
                m.classList.remove('is-open');
                var b = m.closest('[data-lang-dropdown]').querySelector('[data-lang-dropdown-trigger]');
                if (b) b.setAttribute('aria-expanded', 'false');
            }
        });

        // Toggle this one
        menu.classList.toggle('is-open', !isOpen);
        trigger.setAttribute('aria-expanded', !isOpen);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.lang-dropdown__menu.is-open').forEach(function (menu) {
                menu.classList.remove('is-open');
                var btn = menu.closest('[data-lang-dropdown]').querySelector('[data-lang-dropdown-trigger]');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            });
        }
    });
})();