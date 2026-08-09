const sidebar = () => document.querySelector('[data-sidebar]');
const sidebarBackdrop = () => document.querySelector('[data-sidebar-backdrop]');
const sidebarOpeners = () => document.querySelectorAll('[data-sidebar-open]');

function setSidebarOpen(open) {
    const element = sidebar();

    if (! element) {
        return;
    }

    element.dataset.open = open ? 'true' : 'false';

    const backdrop = sidebarBackdrop();
    if (backdrop) {
        backdrop.dataset.open = open ? 'true' : 'false';
    }

    sidebarOpeners().forEach((button) => {
        button.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    document.body.style.overflow = open && window.innerWidth < 1024 ? 'hidden' : '';
}

function closeSidebar() {
    setSidebarOpen(false);
}

document.addEventListener('click', (event) => {
    if (event.target.closest('[data-sidebar-open]')) {
        setSidebarOpen(true);
        return;
    }

    if (event.target.closest('[data-sidebar-close], [data-sidebar-backdrop]')) {
        closeSidebar();
        return;
    }

    if (event.target.closest('[data-sidebar] a[wire\\:navigate]')) {
        closeSidebar();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeSidebar();
    }
});

document.addEventListener('livewire:navigate', closeSidebar);
document.addEventListener('livewire:navigated', closeSidebar);

window.addEventListener('resize', () => {
    if (window.innerWidth >= 1024) {
        closeSidebar();
    }
});
