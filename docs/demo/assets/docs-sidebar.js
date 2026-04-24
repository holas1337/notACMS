// =============================================================================
// docs-sidebar.js — Mobile sidebar drawer toggle on doc pages.
// Toggles .is-open on #docs-sidebar-mobile when .docs-sidebar-toggle is clicked.
// =============================================================================

document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.querySelector('.docs-sidebar-toggle');
    var drawer = document.getElementById('docs-sidebar-mobile');
    if (!toggle || !drawer) return;

    toggle.addEventListener('click', function () {
        var open = drawer.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    // Close drawer when a link inside it is clicked
    drawer.addEventListener('click', function (e) {
        if (e.target.closest('a')) {
            drawer.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });
});
