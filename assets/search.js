(function () {
    'use strict';

    window.addEventListener('DOMContentLoaded', function () {
        var el = document.getElementById('search');
        if (!el) return;

        var placeholder  = el.dataset.placeholder || '';
        var zeroResults  = el.dataset.zeroResults || 'No results for [SEARCH_TERM]';
        var readMoreText = el.dataset.readMore     || 'Read more';
        var tagBaseUrl   = el.dataset.tagBase      || '/tag/tag-placeholder/';
        var imageWidths  = (el.dataset.imageWidths || '640,960').split(',').map(Number);

        var inputWrap = document.createElement('div');
        inputWrap.className = 'search-page__form';
        inputWrap.innerHTML = '<input type="search" class="search-page__input" placeholder="' + esc(placeholder) + '" autocomplete="off">';
        var resultsEl = document.createElement('div');
        resultsEl.className = 'search-page__results';
        el.appendChild(inputWrap);
        el.appendChild(resultsEl);

        var input = inputWrap.querySelector('input');
        var pagefind = null;

        import('/pagefind/pagefind.js').then(function (pf) {
            pagefind = pf;
            var params = new URLSearchParams(window.location.search);
            var q = params.get('q') || '';
            if (q) { input.value = q; doSearch(q); }
        });

        var timer;
        input.addEventListener('input', function () {
            clearTimeout(timer);
            var q = input.value.trim();
            timer = setTimeout(function () { doSearch(q); }, 250);
        });

        function doSearch(query) {
            if (!pagefind || !query) { resultsEl.innerHTML = ''; return; }
            pagefind.search(query).then(function (search) {
                var top = search.results.slice(0, 10);
                Promise.all(top.map(function (r) { return r.data(); })).then(function (items) {
                    render(items, query);
                });
            });
        }

        function render(items, query) {
            if (!items.length) {
                resultsEl.innerHTML = '<p class="search-page__no-results">' +
                    esc(zeroResults.replace('[SEARCH_TERM]', query)) + '</p>';
                return;
            }
            resultsEl.innerHTML = items.map(function (r) {
                var category = r.meta.category || '';
                var date     = r.meta.date || '';
                var image    = r.meta.image || '';
                var title    = r.meta.title || r.url;
                var tags     = (r.meta.tags || '').split(',').map(function (t) { return t.trim(); }).filter(Boolean);
                var tagBase  = tagBaseUrl;

                var metaParts = [];
                if (category) metaParts.push('<span class="meta-category">' + esc(category) + '</span>');
                if (date)     metaParts.push(esc(date));

                var tagsHtml = '';
                if (tags.length) {
                    tagsHtml = '<div class="post-tags">' +
                        tags.map(function (t) {
                            return '<a href="' + esc(tagBase.replace('tag-placeholder', t)) + '" class="tag">' + esc(t) + '</a>';
                        }).join('') +
                        '</div>';
                }

                var srcset = imageWidths.map(function (w) {
                    return esc(image.replace('.webp', '-' + w + 'w.webp')) + ' ' + w + 'w';
                }).join(', ') + ', ' + esc(image) + ' 1280w';

                return '<article class="search-result">' +
                    (image ? '<div class="search-result__image"><a href="' + esc(r.url) + '">' +
                        '<img src="' + esc(image) +
                        '" srcset="' + srcset + '"' +
                        ' sizes="(max-width: 48em) 80px, 120px"' +
                        ' alt="' + esc(title) + '" loading="lazy"></a></div>' : '') +
                    '<div class="search-result__body">' +
                        '<h2 class="post-card-title"><a href="' + esc(r.url) + '">' + esc(title) + '</a></h2>' +
                        (metaParts.length ? '<p class="post-card-meta">' + metaParts.join('&nbsp;&bull;&nbsp;') + '</p>' : '') +
                        '<p class="post-card-excerpt">' + esc(r.excerpt) + '</p>' +
                        tagsHtml +
                        '<a href="' + esc(r.url) + '" class="read-more">' + esc(readMoreText) + ' &rarr;</a>' +
                    '</div>' +
                    '</article>';
            }).join('');
        }

        function esc(s) {
            return String(s)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }
    });
}());
