import '@fortawesome/fontawesome-free/css/all.min.css';
import Swal from 'sweetalert2';

const sidebar = document.querySelector('#app-sidebar');
const backdrop = document.querySelector('#sidebar-backdrop');

const closeSidebar = () => {
    sidebar?.classList.add('-translate-x-full');
    backdrop?.classList.add('hidden');
};

document.querySelector('[data-sidebar-open]')?.addEventListener('click', () => {
    sidebar?.classList.remove('-translate-x-full');
    backdrop?.classList.remove('hidden');
});
document.querySelector('[data-sidebar-close]')?.addEventListener('click', closeSidebar);
backdrop?.addEventListener('click', closeSidebar);

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-sidebar-group-toggle]');

    if (!toggle) {
        return;
    }

    const group = toggle.closest('[data-sidebar-group]');
    const submenu = group?.querySelector('[data-sidebar-submenu]');
    const chevron = group?.querySelector('[data-sidebar-chevron]');
    const open = submenu?.classList.toggle('hidden') === false;

    toggle.setAttribute('aria-expanded', String(open));
    chevron?.classList.toggle('rotate-180', open);
});

document.querySelector('[data-theme-toggle]')?.addEventListener('click', () => {
    const dark = !document.documentElement.classList.contains('dark');
    document.documentElement.classList.toggle('dark', dark);
    localStorage.setItem('admin_panel_theme', dark ? 'dark' : 'light');
});

const applyTheme = (theme) => {
    const dark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', dark);
    localStorage.setItem('admin_panel_theme', theme);
};

document.querySelectorAll('[data-theme-choice]').forEach((button) => {
    button.addEventListener('click', () => {
        applyTheme(button.dataset.themeChoice);
        document.querySelectorAll('[data-theme-choice]').forEach((choice) => choice.classList.remove('border-emerald-500', 'ring-2', 'ring-emerald-500/20'));
        button.classList.add('border-emerald-500', 'ring-2', 'ring-emerald-500/20');
    });
});

const selectedTheme = localStorage.getItem('admin_panel_theme') || 'light';
document.querySelector(`[data-theme-choice="${selectedTheme}"]`)?.classList.add('border-emerald-500', 'ring-2', 'ring-emerald-500/20');

document.querySelectorAll('[data-confirm-form]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        if (form.dataset.confirmed === 'true') {
            return;
        }

        event.preventDefault();
        const dark = document.documentElement.classList.contains('dark');
        const result = await Swal.fire({
            title: form.dataset.confirmTitle,
            text: form.dataset.confirmText || undefined,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: form.dataset.confirmButton || 'Confirmar',
            cancelButtonText: form.dataset.cancelButton || 'Cancelar',
            reverseButtons: true,
            focusCancel: true,
            background: dark ? '#0f2f25' : '#ffffff',
            color: dark ? '#ecfdf5' : '#0f172a',
            iconColor: '#059669',
            confirmButtonColor: '#047857',
            cancelButtonColor: dark ? '#334155' : '#64748b',
        });

        if (result.isConfirmed) {
            form.dataset.confirmed = 'true';
            form.requestSubmit();
        }
    });
});

const closeModal = (modal) => {
    modal?.classList.add('hidden');
    modal?.setAttribute('aria-hidden', 'true');

    if (!document.querySelector('[data-modal]:not(.hidden)')) {
        document.body.classList.remove('overflow-y-hidden');
    }
};

document.querySelectorAll('[data-modal-open]').forEach((button) => {
    button.addEventListener('click', () => {
        const modal = document.querySelector(`#${button.dataset.modalOpen}`);

        if (!modal) {
            return;
        }

        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-y-hidden');
        modal.querySelector('[data-modal-autofocus]')?.focus();
    });
});

document.querySelectorAll('[data-modal]').forEach((modal) => {
    modal.querySelectorAll('[data-modal-close]').forEach((button) => {
        button.addEventListener('click', () => closeModal(modal));
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        document.querySelectorAll('[data-modal]:not(.hidden)').forEach(closeModal);
    }
});

const loadingOverlay = document.querySelector('[data-loading-overlay]');
const loadingMessage = loadingOverlay?.querySelector('[data-loading-message]');

const showLoading = (message = loadingOverlay?.dataset.defaultMessage || 'Procesando solicitud...') => {
    if (!loadingOverlay) {
        return;
    }

    loadingMessage.textContent = message;
    loadingOverlay.classList.remove('hidden');
    loadingOverlay.classList.add('flex');
    loadingOverlay.setAttribute('aria-busy', 'true');
    loadingOverlay.setAttribute('aria-hidden', 'false');
    document.body.classList.add('overflow-y-hidden');
};

const hideLoading = () => {
    if (!loadingOverlay) {
        return;
    }

    loadingOverlay.classList.add('hidden');
    loadingOverlay.classList.remove('flex');
    loadingOverlay.setAttribute('aria-busy', 'false');
    loadingOverlay.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('overflow-y-hidden');
};

window.appLoading = { show: showLoading, hide: hideLoading };

document.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-loading-form]');

    if (!form || (form.dataset.confirmForm !== undefined && form.dataset.confirmed !== 'true')) {
        return;
    }

    showLoading(form.dataset.loadingMessage);
});

document.addEventListener('click', (event) => {
    const element = event.target.closest('[data-loading-start]');

    if (element) {
        showLoading(element.dataset.loadingMessage);
    }
});

window.addEventListener('app:loading:start', (event) => showLoading(event.detail?.message));
window.addEventListener('app:loading:stop', hideLoading);
window.addEventListener('pageshow', hideLoading);
