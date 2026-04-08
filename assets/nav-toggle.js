document.addEventListener('DOMContentLoaded', () => {
    const btn = document.querySelector('.nav-toggle');
    const nav = document.getElementById('nav-list');
    if (!btn || !nav) return;

    const close = () => {
        nav.classList.remove('is-open');
        btn.setAttribute('aria-expanded', 'false');
    };

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const open = nav.classList.toggle('is-open');
        btn.setAttribute('aria-expanded', open);
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.site-nav')) close();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') close();
    });
});
