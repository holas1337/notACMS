document.addEventListener('DOMContentLoaded', function () {
    var html = document.documentElement;
    var labelCopy = html.dataset.codeCopyLabel || 'copy';
    var labelCopied = html.dataset.codeCopiedLabel || 'copied!';
    var ariaLabel = html.dataset.codeCopyAria || 'Copy code';

    document.querySelectorAll('pre > code').forEach(function (code) {
        var pre = code.parentElement;
        var btn = document.createElement('button');
        btn.className = 'code-copy-btn';
        btn.setAttribute('aria-label', ariaLabel);
        btn.textContent = labelCopy;
        btn.addEventListener('click', function () {
            navigator.clipboard.writeText(code.textContent).then(function () {
                btn.textContent = labelCopied;
                setTimeout(function () { btn.textContent = labelCopy; }, 2000);
            });
        });
        pre.appendChild(btn);
    });
});
