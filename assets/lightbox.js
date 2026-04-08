document.addEventListener('DOMContentLoaded', () => {
    if (!document.querySelector('.post-content img[data-full], .page-body img[data-full]')) return;

    const overlay = document.createElement('div');
    overlay.id = 'lightbox';
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-modal', 'true');
    overlay.setAttribute('aria-label', 'Image lightbox');
    overlay.innerHTML = `<img alt="">
        <button class="lb-close" aria-label="Close">&times;</button>
        <div class="lb-zoom-controls">
            <button class="lb-zoom-out" aria-label="Zoom out">&#8722;</button>
            <button class="lb-zoom-in" aria-label="Zoom in">&#43;</button>
        </div>`;
    document.body.appendChild(overlay);

    const img = overlay.querySelector('img');
    const btnClose = overlay.querySelector('.lb-close');
    const btnZoomIn = overlay.querySelector('.lb-zoom-in');
    const btnZoomOut = overlay.querySelector('.lb-zoom-out');
    const focusableSelectors = 'button, [href], input, [tabindex]:not([tabindex="-1"])';

    // --- Zoom/pan state ---
    let scale = 1;
    let ox = 0, oy = 0;
    let dragging = false;
    let dragStartX, dragStartY, dragOx, dragOy;
    let clickMoved = false;
    let openerEl = null;

    function applyTransform() {
        img.style.transform = `scale(${scale}) translate(${ox}px, ${oy}px)`;
        img.style.cursor = scale > 1 ? 'grab' : 'zoom-in';
    }

    function resetZoom() {
        scale = 1; ox = 0; oy = 0;
        applyTransform();
    }

    // --- Scroll to zoom ---
    overlay.addEventListener('wheel', e => {
        e.preventDefault();
        const factor = e.deltaY < 0 ? 1.15 : 1 / 1.15;
        scale = Math.min(Math.max(scale * factor, 1), 5);
        if (1 === scale) { ox = 0; oy = 0; }
        applyTransform();
    }, { passive: false });

    // --- Drag to pan ---
    img.addEventListener('mousedown', e => {
        clickMoved = false;
        if (scale <= 1) return;
        dragging = true;
        dragStartX = e.clientX;
        dragStartY = e.clientY;
        dragOx = ox;
        dragOy = oy;
        img.style.cursor = 'grabbing';
        e.preventDefault();
    });

    document.addEventListener('mousemove', e => {
        if (!dragging) return;
        const dx = e.clientX - dragStartX;
        const dy = e.clientY - dragStartY;
        if (Math.abs(dx) > 3 || Math.abs(dy) > 3) clickMoved = true;
        ox = dragOx + dx / scale;
        oy = dragOy + dy / scale;
        img.style.transform = `scale(${scale}) translate(${ox}px, ${oy}px)`;
    });

    document.addEventListener('mouseup', () => {
        if (!dragging) return;
        dragging = false;
        img.style.cursor = scale > 1 ? 'grab' : 'zoom-in';
    });

    // --- Click to toggle zoom ---
    img.addEventListener('click', () => {
        if (clickMoved) return;
        if (scale > 1) { resetZoom(); return; }
        scale = 2; ox = 0; oy = 0;
        applyTransform();
    });

    img.addEventListener('dblclick', resetZoom);

    // --- +/- buttons ---
    btnZoomIn.addEventListener('click', e => {
        e.stopPropagation();
        scale = Math.min(scale * 1.5, 5);
        applyTransform();
    });

    btnZoomOut.addEventListener('click', e => {
        e.stopPropagation();
        scale = Math.max(scale / 1.5, 1);
        if (1 === scale) { ox = 0; oy = 0; }
        applyTransform();
    });

    // --- Focus trap ---
    overlay.addEventListener('keydown', e => {
        if (e.key !== 'Tab') return;
        const focusable = Array.from(overlay.querySelectorAll(focusableSelectors));
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (e.shiftKey) {
            if (document.activeElement === first) { e.preventDefault(); last.focus(); }
        } else {
            if (document.activeElement === last) { e.preventDefault(); first.focus(); }
        }
    });

    // --- Open ---
    document.querySelectorAll('.post-content img, .page-body img').forEach(el => {
        const full = el.dataset.full;
        if (!full) return;

        el.style.cursor = 'zoom-in';
        el.addEventListener('click', () => {
            openerEl = el;
            img.src = full;
            img.alt = el.alt;
            resetZoom();
            overlay.classList.add('is-open');
            btnClose.focus();
        });
    });

    // --- Close ---
    const close = () => {
        overlay.classList.remove('is-open');
        resetZoom();
        if (openerEl) { openerEl.focus(); openerEl = null; }
    };

    btnClose.addEventListener('click', close);
    overlay.addEventListener('click', e => { if (e.target === overlay) close(); });
    document.addEventListener('keydown', e => { if ('Escape' === e.key && overlay.classList.contains('is-open')) close(); });
});
