import './bootstrap';
import { initTheme } from './theme';
import { initSidebar } from './sidebar';

initTheme();
initSidebar();

// Konfirmasi aksi destruktif: <form data-confirm="Hapus data ini?">
document.addEventListener('submit', (event) => {
    const form = event.target.closest('form[data-confirm]');
    if (form && !window.confirm(form.dataset.confirm)) {
        event.preventDefault();
    }
});
