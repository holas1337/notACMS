document.querySelectorAll('.recommendation-expand').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var full = btn.nextElementSibling;
        if (!full) return;
        var opening = full.hidden;
        full.hidden = !opening;
        btn.setAttribute('aria-expanded', opening ? 'true' : 'false');
        btn.textContent = opening ? btn.dataset.collapse : btn.dataset.expand;
    });
});
