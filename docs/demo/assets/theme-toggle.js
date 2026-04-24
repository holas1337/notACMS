// =============================================================================
// theme-toggle.js — Dark / light mode toggle.
// Persists to localStorage under 'notacms-theme'.
// Falls back to prefers-color-scheme on first visit.
// Sets data-theme on <html> and swaps ph-moon ↔ ph-sun icon.
// =============================================================================

const STORAGE_KEY = 'notacms-theme';
const THEMES = { light: 'light', dark: 'dark' };

function getSystemTheme() {
    return window.matchMedia('(prefers-color-scheme: dark)').matches
        ? THEMES.dark
        : THEMES.light;
}

function getSavedTheme() {
    try {
        return localStorage.getItem(STORAGE_KEY) || null;
    } catch {
        return null;
    }
}

function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
}

var toggleButtons = [];

function updateToggleButton(theme) {
    toggleButtons.forEach((btn) => {
        const icon = btn.querySelector('i');
        if (!icon) return;

        if (theme === THEMES.dark) {
            icon.className = 'ph ph-sun';
            btn.setAttribute('aria-label', btn.dataset.labelLight || 'Switch to light mode');
        } else {
            icon.className = 'ph ph-moon';
            btn.setAttribute('aria-label', btn.dataset.labelDark || 'Switch to dark mode');
        }
    });
}

function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme') || THEMES.light;
    const next = current === THEMES.dark ? THEMES.light : THEMES.dark;

    applyTheme(next);
    updateToggleButton(next);

    try {
        localStorage.setItem(STORAGE_KEY, next);
    } catch {
        // Storage unavailable — continue without persistence
    }
}

// Apply theme immediately (before DOM ready to avoid flash)
const initial = getSavedTheme() || getSystemTheme();
applyTheme(initial);

document.addEventListener('DOMContentLoaded', () => {
    toggleButtons = Array.from(document.querySelectorAll('.theme-toggle'));
    updateToggleButton(initial);

    toggleButtons.forEach((btn) => {
        btn.addEventListener('click', toggleTheme);
    });

    // Keep in sync if user changes system preference in another tab
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!getSavedTheme()) {
            const theme = e.matches ? THEMES.dark : THEMES.light;
            applyTheme(theme);
            updateToggleButton(theme);
        }
    });
});
