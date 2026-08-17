const sidebar = () => document.querySelector('[data-sidebar]');
const sidebarBackdrop = () => document.querySelector('[data-sidebar-backdrop]');
const sidebarOpeners = () => document.querySelectorAll('[data-sidebar-open]');
let lastSidebarOpener = null;
let focusMainAfterNavigation = false;

function focusSidebar() {
    const element = sidebar();
    const target = element?.querySelector('[data-sidebar-close], a, button, input, select, textarea');

    target?.focus({ preventScroll: true });
}

function syncSidebarAccessibility(open) {
    const element = sidebar();

    if (! element) {
        return;
    }

    const mobileClosed = ! open && window.innerWidth < 1024;

    element.toggleAttribute('inert', mobileClosed);

    element.setAttribute('aria-hidden', mobileClosed ? 'true' : 'false');
}

function setSidebarOpen(open) {
    const element = sidebar();

    if (! element) {
        return;
    }

    syncSidebarAccessibility(open);
    element.dataset.open = open ? 'true' : 'false';

    const backdrop = sidebarBackdrop();
    if (backdrop) {
        backdrop.dataset.open = open ? 'true' : 'false';
    }

    sidebarOpeners().forEach((button) => {
        button.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    document.body.style.overflow = open && window.innerWidth < 1024 ? 'hidden' : '';

    if (open) {
        window.requestAnimationFrame(focusSidebar);
    } else if (lastSidebarOpener) {
        lastSidebarOpener.focus({ preventScroll: true });
        lastSidebarOpener = null;
    }
}

syncSidebarAccessibility(false);

function syncSectionTabs() {
    document.querySelectorAll('[data-section-tabs]').forEach((navigation) => {
        const tabs = [...navigation.querySelectorAll('[data-section-tab]')];
        const activeHash = window.location.hash || tabs[0]?.getAttribute('href');

        tabs.forEach((tab) => {
            const active = tab.getAttribute('href') === activeHash;

            tab.toggleAttribute('aria-current', active);
            tab.classList.toggle('border-workspace-accent', active);
            tab.classList.toggle('text-workspace-text', active);
            tab.classList.toggle('border-transparent', ! active);
            tab.classList.toggle('text-workspace-muted', ! active);
        });
    });
}

syncSectionTabs();

function closeSidebar() {
    setSidebarOpen(false);
}

document.addEventListener('click', (event) => {
    if (event.target.closest('[data-sidebar-open]')) {
        lastSidebarOpener = event.target.closest('[data-sidebar-open]');
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

document.addEventListener('livewire:navigate', () => {
    focusMainAfterNavigation = true;
    closeSidebar();
});
document.addEventListener('livewire:navigated', () => {
    closeSidebar();
    syncSectionTabs();

    if (! focusMainAfterNavigation) {
        return;
    }

    focusMainAfterNavigation = false;
    window.requestAnimationFrame(() => document.querySelector('[data-route-focus]')?.focus({ preventScroll: true }));
});

window.addEventListener('resize', () => {
    const element = sidebar();

    if (! element) {
        return;
    }

    if (window.innerWidth >= 1024) {
        closeSidebar();
        return;
    }

    syncSidebarAccessibility(element.dataset.open === 'true');
});

window.addEventListener('hashchange', syncSectionTabs);
