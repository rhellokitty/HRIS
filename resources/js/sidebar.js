/**
 * Drawer sidebar untuk layout Admin pada layar < lg.
 */
export function initSidebar() {
    const sidebar = document.querySelector('[data-sidebar]');
    const overlay = document.querySelector('[data-sidebar-overlay]');
    if (!sidebar) return;

    const open = () => {
        sidebar.classList.remove('-translate-x-full');
        overlay?.classList.remove('hidden');
        document.documentElement.style.overflow = 'hidden';
    };

    const close = () => {
        sidebar.classList.add('-translate-x-full');
        overlay?.classList.add('hidden');
        document.documentElement.style.overflow = '';
    };

    document.querySelectorAll('[data-sidebar-open]').forEach((btn) => btn.addEventListener('click', open));
    document.querySelectorAll('[data-sidebar-close]').forEach((btn) => btn.addEventListener('click', close));
    overlay?.addEventListener('click', close);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') close();
    });

    window.matchMedia('(min-width: 1024px)').addEventListener('change', (mq) => {
        if (mq.matches) close();
    });
}
