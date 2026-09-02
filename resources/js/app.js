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

document.querySelectorAll('[data-sidebar-group-toggle]').forEach((toggle) => {
    toggle.addEventListener('click', () => {
        const group = toggle.closest('[data-sidebar-group]');
        const submenu = group?.querySelector('[data-sidebar-submenu]');
        const chevron = group?.querySelector('[data-sidebar-chevron]');
        const open = submenu?.classList.toggle('hidden') === false;

        toggle.setAttribute('aria-expanded', String(open));
        chevron?.classList.toggle('rotate-180', open);
    });
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
