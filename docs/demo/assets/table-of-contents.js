document.addEventListener('DOMContentLoaded', function () {
    var label = document.documentElement.dataset.tocLabel || 'Table of Contents';

    document.querySelectorAll('.post-content, .page-body').forEach(function (content) {
        var headings = content.querySelectorAll('h2, h3');
        if (headings.length < 3) { return; }

        var details = document.createElement('details');
        details.className = 'toc';
        details.open = false;

        var summary = document.createElement('summary');
        summary.className = 'toc__title';
        summary.textContent = label;
        details.appendChild(summary);

        var nav = document.createElement('nav');
        nav.setAttribute('aria-label', label);
        var rootOl = document.createElement('ol');
        rootOl.className = 'toc__list';

        var currentH2Li = null;
        var currentSubOl = null;

        headings.forEach(function (heading) {
            var permalinkAnchor = heading.querySelector('.heading-anchor');
            var id = permalinkAnchor ? permalinkAnchor.id : heading.id;
            if (!id) { return; }

            var clone = heading.cloneNode(true);
            var cloneAnchor = clone.querySelector('.heading-anchor');
            if (cloneAnchor) { cloneAnchor.remove(); }
            var text = clone.textContent.trim();

            var li = document.createElement('li');
            li.className = 'toc__item';
            var a = document.createElement('a');
            a.className = 'toc__link';
            a.href = '#' + id;
            a.textContent = text;
            li.appendChild(a);

            if (heading.tagName === 'H2') {
                currentSubOl = null;
                currentH2Li = li;
                rootOl.appendChild(li);
            } else {
                if (!currentSubOl) {
                    currentSubOl = document.createElement('ol');
                    currentSubOl.className = 'toc__sublist';
                    if (currentH2Li) {
                        currentH2Li.appendChild(currentSubOl);
                    } else {
                        rootOl.appendChild(currentSubOl);
                    }
                }
                currentSubOl.appendChild(li);
            }
        });

        nav.appendChild(rootOl);
        details.appendChild(nav);
        content.insertBefore(details, content.firstChild);
    });
});
