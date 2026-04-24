// =============================================================================
// docs-toc.js — Populates the right-sidebar TOC on doc pages.
// Reads h2 / h3 headings from .docs-content .prose and generates .toc-list.
// Uses IntersectionObserver to track the active heading while scrolling.
// =============================================================================

document.addEventListener('DOMContentLoaded', function () {
    var tocList = document.getElementById('docs-toc-list');
    var prose = document.querySelector('.docs-content .prose');
    if (!tocList || !prose) return;

    var headings = Array.from(prose.querySelectorAll('h2, h3'));
    if (headings.length < 2) {
        var tocPanel = document.querySelector('.docs-toc');
        if (tocPanel) tocPanel.classList.add('is-hidden');
        return;
    }

    // CommonMark renderer puts id on the <a> inside the heading, not on the <h2> itself
    function getHeadingId(h) {
        if (h.id) return h.id;
        var anchor = h.querySelector('a[id]');
        return anchor ? anchor.id : null;
    }

    // Build TOC items
    var fragment = document.createDocumentFragment();
    headings.forEach(function (h) {
        var id = getHeadingId(h);
        if (!id) return;

        var li = document.createElement('li');
        if (h.tagName === 'H3') li.className = 'toc-h3';

        var a = document.createElement('a');
        a.href = '#' + id;
        // Strip any permalink anchor text
        var clone = h.cloneNode(true);
        var perma = clone.querySelector('a.heading-anchor, a.heading-permalink');
        if (perma) perma.remove();
        a.textContent = clone.textContent.trim();

        li.appendChild(a);
        fragment.appendChild(li);
    });
    tocList.appendChild(fragment);

    // Intersection observer — mark the topmost visible heading as active
    var links = Array.from(tocList.querySelectorAll('a'));
    var activeId = null;

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                var id = getHeadingId(entry.target);
                if (id !== activeId) {
                    activeId = id;
                    links.forEach(function (a) {
                        a.classList.toggle('is-active', a.getAttribute('href') === '#' + id);
                    });
                }
            }
        });
    }, {
        rootMargin: '-10% 0px -80% 0px',
        threshold: 0
    });

    headings.forEach(function (h) {
        var id = getHeadingId(h);
        if (id) observer.observe(h);
    });

    // Smooth scroll for TOC links
    tocList.addEventListener('click', function (e) {
        var link = e.target.closest('a');
        if (!link) return;
        var target = document.getElementById(link.getAttribute('href').slice(1));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth' });
            history.pushState(null, '', link.getAttribute('href'));
        }
    });
});
