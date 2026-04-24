(function () {
    'use strict';

    var bar = document.createElement('div');
    bar.className = 'reading-progress-bar';
    document.body.appendChild(bar);

    function update() {
        var article = document.querySelector('article[data-pagefind-body]');
        if (!article) {
            return;
        }

        var rect = article.getBoundingClientRect();
        var articleHeight = article.offsetHeight;
        var scrolled = -rect.top;
        var total = articleHeight - window.innerHeight;

        if (total <= 0) {
            bar.style.width = '100%';

            return;
        }

        var pct = Math.min(100, Math.max(0, (scrolled / total) * 100));
        bar.style.width = pct + '%';
    }

    document.addEventListener('scroll', update, { passive: true });
    update();
}());
