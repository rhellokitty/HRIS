{{--
    HRIS Custom Confirmation Modal (Design PRD §2.1, §5.2)
    - Pure borderless glassmorphism (var(--bg-card), backdrop blur 20px)
    - Zero box-shadow & border: none guarantee
    - Semantic Corporate Navy / Electric Blue / Danger Rose palette
    - Fully accessible (Keyboard Escape to cancel, Enter to confirm, focus return)
--}}
<div id="hris-confirm-modal"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-md opacity-0 pointer-events-none transition-opacity duration-200"
     role="dialog"
     aria-modal="true"
     aria-hidden="true"
     aria-labelledby="hris-confirm-modal-title"
     aria-describedby="hris-confirm-modal-desc">

    <div id="hris-confirm-dialog"
         class="glass-card relative w-full max-w-md transform p-6 md:p-7 text-center rounded-xl scale-95 opacity-0 transition-all duration-200 ease-hris">

        {{-- Close button --}}
        <button type="button"
                id="hris-modal-close-btn"
                class="btn-ghost btn-sm absolute top-4 right-4 text-hris-text-muted hover:text-hris-text !p-1.5"
                aria-label="Tutup dialog konfirmasi">
            <x-icon name="x" class="w-4 h-4" />
        </button>

        {{-- Icon Badge Wrapper --}}
        <div id="hris-modal-icon-badge"
             class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-hris-danger-soft text-hris-danger transition-colors duration-200">
            <span id="hris-icon-trash" class="flex items-center justify-center">
                <x-icon name="trash" class="w-7 h-7" />
            </span>
            <span id="hris-icon-sparkles" class="hidden items-center justify-center">
                <x-icon name="sparkles" class="w-7 h-7" />
            </span>
            <span id="hris-icon-exclamation" class="hidden items-center justify-center">
                <x-icon name="exclamation" class="w-7 h-7" />
            </span>
            <span id="hris-icon-info" class="hidden items-center justify-center">
                <x-icon name="info" class="w-7 h-7" />
            </span>
        </div>

        {{-- Title & Description --}}
        <h3 id="hris-confirm-modal-title"
            class="text-base md:text-lg font-bold text-hris-text tracking-tight">
            Konfirmasi Tindakan
        </h3>
        <p id="hris-confirm-modal-desc"
           class="mt-2 text-xs md:text-sm text-hris-text-secondary leading-relaxed">
            Apakah Anda yakin ingin melanjutkan tindakan ini?
        </p>

        {{-- Action Buttons --}}
        <div class="mt-6 flex flex-col-reverse sm:flex-row items-center justify-end gap-2.5 w-full">
            <button type="button"
                    id="hris-modal-cancel-btn"
                    class="btn-surface w-full sm:w-1/2 py-2.5 text-xs font-semibold">
                Batal
            </button>
            <button type="button"
                    id="hris-modal-confirm-btn"
                    class="btn-danger w-full sm:w-1/2 py-2.5 text-xs font-semibold flex items-center justify-center gap-1.5">
                <span id="hris-modal-confirm-text">Lanjutkan</span>
            </button>
        </div>
    </div>
</div>
