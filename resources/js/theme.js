/**
 * Theme switcher (Dark: Executive Midnight Navy — default, Light: Crisp Corporate Slate).
 * Atribut `data-theme` di <html> sudah diset lebih awal oleh inline script di <head>
 * agar tidak terjadi flash; modul ini hanya menangani tombol toggle.
 */
const STORAGE_KEY = 'hris-theme';

export function currentTheme() {
    return document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
}

export function applyTheme(theme) {
    const value = theme === 'light' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', value);
    document.documentElement.classList.toggle('dark', value === 'dark');

    try {
        localStorage.setItem(STORAGE_KEY, value);
    } catch (e) {
        /* storage tidak tersedia (private mode) */
    }

    document.querySelectorAll('[data-theme-label]').forEach((el) => {
        el.textContent = value === 'dark' ? 'Gelap' : 'Terang';
    });
}

export function initTheme() {
    applyTheme(currentTheme());

    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('[data-theme-toggle]');
        if (!toggle) return;

        event.preventDefault();
        applyTheme(currentTheme() === 'dark' ? 'light' : 'dark');
    });
}
