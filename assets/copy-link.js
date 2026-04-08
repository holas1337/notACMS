(function () {
    var btn = document.querySelector('.share-btn--copy');
    if (!btn) return;

    btn.addEventListener('click', function () {
        var url = btn.getAttribute('data-url');
        var label = btn.getAttribute('data-label');
        var copied = btn.getAttribute('data-copied');

        navigator.clipboard.writeText(url).then(function () {
            btn.textContent = copied;
            setTimeout(function () {
                btn.textContent = label;
            }, 1500);
        });
    });
})();
