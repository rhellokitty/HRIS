/**
 * HRIS Custom Confirmation Modal Controller (Design PRD §2.1, §5.2)
 *
 * Menghilangkan sepenuhnya window.confirm() dan window.alert() native browser.
 * Menyediakan dialog konfirmasi borderless berstandar Apple/visionOS glassmorphism
 * dengan dukungan keyboard accessibility (Escape, Enter) & declarative HTML data attributes.
 */

let modalEl = null;
let dialogEl = null;
let titleEl = null;
let descEl = null;
let iconBadgeEl = null;
let confirmBtnEl = null;
let confirmTextEl = null;
let cancelBtnEl = null;
let closeBtnEl = null;

let currentResolve = null;
let lastFocusedElement = null;

export function initConfirmModal() {
    modalEl = document.getElementById('hris-confirm-modal');
    if (!modalEl) return;

    dialogEl = document.getElementById('hris-confirm-dialog');
    titleEl = document.getElementById('hris-confirm-modal-title');
    descEl = document.getElementById('hris-confirm-modal-desc');
    iconBadgeEl = document.getElementById('hris-modal-icon-badge');
    confirmBtnEl = document.getElementById('hris-modal-confirm-btn');
    confirmTextEl = document.getElementById('hris-modal-confirm-text');
    cancelBtnEl = document.getElementById('hris-modal-cancel-btn');
    closeBtnEl = document.getElementById('hris-modal-close-btn');

    const handleDismiss = () => closeModal(false);

    if (cancelBtnEl) cancelBtnEl.addEventListener('click', handleDismiss);
    if (closeBtnEl) closeBtnEl.addEventListener('click', handleDismiss);

    // Klik di area backdrop (luar dialog) menutup modal
    modalEl.addEventListener('click', (event) => {
        if (event.target === modalEl) {
            handleDismiss();
        }
    });

    // Klik tombol konfirmasi
    if (confirmBtnEl) {
        confirmBtnEl.addEventListener('click', () => {
            closeModal(true);
        });
    }

    // Aksesibilitas Keyboard: Escape untuk batal, Enter untuk setuju
    document.addEventListener('keydown', (event) => {
        if (!isModalOpen()) return;

        if (event.key === 'Escape') {
            event.preventDefault();
            handleDismiss();
        } else if (event.key === 'Enter' && !event.shiftKey && !event.ctrlKey) {
            if (document.activeElement === cancelBtnEl || document.activeElement === closeBtnEl) {
                event.preventDefault();
                handleDismiss();
                return;
            }
            event.preventDefault();
            closeModal(true);
        }
    });

    // Deklaratif: tangkap submit form yang memiliki atribut data-confirm
    document.addEventListener('submit', (event) => {
        const form = event.target.closest('form[data-confirm]');
        if (!form) return;

        // Jika form sudah disetujui sebelumnya, biarkan submit native berjalan
        if (form.__hrisConfirmed) {
            delete form.__hrisConfirmed;
            return;
        }

        event.preventDefault();

        const message = form.dataset.confirmMessage || form.dataset.confirm || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
        const isDelete = message.toLowerCase().includes('hapus');
        const isReject = message.toLowerCase().includes('tolak');
        const isGenerate = message.toLowerCase().includes('generate');

        let defaultTitle = 'Konfirmasi Tindakan';
        if (isDelete) defaultTitle = 'Konfirmasi Hapus Data';
        else if (isReject) defaultTitle = 'Konfirmasi Penolakan';
        else if (isGenerate) defaultTitle = 'Generate Jadwal Otomatis';

        let defaultVariant = 'danger';
        if (isGenerate) defaultVariant = 'primary';

        let defaultBtnText = 'Ya, Lanjutkan';
        if (isDelete) defaultBtnText = 'Ya, Hapus';
        else if (isReject) defaultBtnText = 'Ya, Tolak';
        else if (isGenerate) defaultBtnText = 'Ya, Generate';

        const title = form.dataset.confirmTitle || defaultTitle;
        const confirmBtn = form.dataset.confirmBtn || defaultBtnText;
        const cancelBtn = form.dataset.confirmCancel || 'Batal';
        const variant = form.dataset.confirmVariant || defaultVariant;
        const icon = form.dataset.confirmIcon || (variant === 'primary' ? 'sparkles' : 'trash');

        showConfirmModal({
            title,
            message,
            confirmText: confirmBtn,
            cancelText: cancelBtn,
            variant,
            icon,
        }).then((confirmed) => {
            if (confirmed) {
                form.__hrisConfirmed = true;
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            }
        });
    });

    // Deklaratif: tangkap klik tombol / link dengan data-confirm (bukan form langsung)
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-confirm]:not(form)');
        if (!trigger) return;

        // Jika tombol submit di dalam form data-confirm, serahkan ke listener submit form
        if (trigger.closest('form[data-confirm]')) return;

        const form = trigger.closest('form');
        if (form && trigger.type === 'submit') {
            event.preventDefault();
            form.dataset.confirm = trigger.dataset.confirm;
            if (trigger.dataset.confirmTitle) form.dataset.confirmTitle = trigger.dataset.confirmTitle;
            if (trigger.dataset.confirmMessage) form.dataset.confirmMessage = trigger.dataset.confirmMessage;
            if (trigger.dataset.confirmBtn) form.dataset.confirmBtn = trigger.dataset.confirmBtn;
            if (trigger.dataset.confirmVariant) form.dataset.confirmVariant = trigger.dataset.confirmVariant;
            if (trigger.dataset.confirmIcon) form.dataset.confirmIcon = trigger.dataset.confirmIcon;

            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
            return;
        }

        // Jika anchor link dengan data-confirm
        if (trigger.tagName && trigger.tagName.toLowerCase() === 'a' && trigger.href) {
            event.preventDefault();
            const href = trigger.href;
            showConfirmModal({
                title: trigger.dataset.confirmTitle || 'Konfirmasi Navigasi',
                message: trigger.dataset.confirmMessage || trigger.dataset.confirm,
                confirmText: trigger.dataset.confirmBtn || 'Lanjutkan',
                cancelText: trigger.dataset.confirmCancel || 'Batal',
                variant: trigger.dataset.confirmVariant || 'primary',
                icon: trigger.dataset.confirmIcon || 'info',
            }).then((confirmed) => {
                if (confirmed) {
                    window.location.href = href;
                }
            });
        }
    });

    // Ekspos ke global window untuk kemudahan pemanggilan imperatif jika dibutuhkan
    window.showConfirmModal = showConfirmModal;
}

function isModalOpen() {
    return modalEl && !modalEl.classList.contains('pointer-events-none');
}

export function showConfirmModal(options = {}) {
    return new Promise((resolve) => {
        if (!modalEl) {
            // Fallback aman jika DOM belum terpasang
            resolve(true);
            return;
        }

        currentResolve = resolve;
        lastFocusedElement = document.activeElement;

        const {
            title = 'Konfirmasi Tindakan',
            message = 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
            confirmText = 'Lanjutkan',
            cancelText = 'Batal',
            variant = 'danger',
            icon = 'trash',
        } = options;

        if (titleEl) titleEl.textContent = title;
        if (descEl) descEl.textContent = message;
        if (confirmTextEl) confirmTextEl.textContent = confirmText;
        if (cancelBtnEl) cancelBtnEl.textContent = cancelText;

        applyVariant(variant, icon);

        // Buka modal dengan transisi halus
        modalEl.classList.remove('pointer-events-none', 'opacity-0');
        modalEl.classList.add('pointer-events-auto', 'opacity-100');
        modalEl.setAttribute('aria-hidden', 'false');

        if (dialogEl) {
            dialogEl.classList.remove('scale-95', 'opacity-0');
            dialogEl.classList.add('scale-100', 'opacity-100');
        }

        // Fokuskan tombol konfirmasi setelah render
        setTimeout(() => {
            if (confirmBtnEl) confirmBtnEl.focus();
        }, 50);
    });
}

function applyVariant(variant, iconName) {
    if (!iconBadgeEl || !confirmBtnEl) return;

    // Reset badge
    iconBadgeEl.className = 'mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full transition-colors duration-200';

    // Reset tombol konfirmasi
    confirmBtnEl.className = 'w-full sm:w-1/2 py-2.5 text-xs font-semibold flex items-center justify-center gap-1.5 rounded-md transition-all duration-150 ease-hris select-none';

    if (variant === 'primary') {
        iconBadgeEl.classList.add('bg-hris-primary-soft', 'text-hris-primary');
        confirmBtnEl.classList.add('btn-primary');
    } else if (variant === 'warning') {
        iconBadgeEl.classList.add('bg-hris-warning-soft', 'text-hris-warning');
        confirmBtnEl.classList.add('bg-hris-warning', 'text-white');
    } else {
        // default: danger
        iconBadgeEl.classList.add('bg-hris-danger-soft', 'text-hris-danger');
        confirmBtnEl.classList.add('btn-danger');
    }

    // Toggle icon SVG
    const icons = {
        trash: document.getElementById('hris-icon-trash'),
        sparkles: document.getElementById('hris-icon-sparkles'),
        exclamation: document.getElementById('hris-icon-exclamation'),
        info: document.getElementById('hris-icon-info'),
    };

    Object.values(icons).forEach((el) => {
        if (el) el.classList.add('hidden');
    });

    const activeIconEl = icons[iconName] || (variant === 'primary' ? icons.sparkles : icons.trash);
    if (activeIconEl) {
        activeIconEl.classList.remove('hidden');
    }
}

function closeModal(result) {
    if (!modalEl) return;

    modalEl.classList.remove('pointer-events-auto', 'opacity-100');
    modalEl.classList.add('pointer-events-none', 'opacity-0');
    modalEl.setAttribute('aria-hidden', 'true');

    if (dialogEl) {
        dialogEl.classList.remove('scale-100', 'opacity-100');
        dialogEl.classList.add('scale-95', 'opacity-0');
    }

    if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
        lastFocusedElement.focus();
    }

    if (typeof currentResolve === 'function') {
        currentResolve(result);
        currentResolve = null;
    }
}
