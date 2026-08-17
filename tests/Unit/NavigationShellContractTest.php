<?php

it('keeps the drawer accessibility state transitions in the shell script', function (): void {
    $script = file_get_contents(dirname(__DIR__, 2).'/resources/js/app.js');

    expect($script)->not->toBeFalse()
        ->toContain('const mobileClosed = ! open && window.innerWidth < 1024;')
        ->toContain("element.toggleAttribute('inert', mobileClosed);")
        ->toContain("element.setAttribute('aria-hidden', mobileClosed ? 'true' : 'false');")
        ->toContain('window.requestAnimationFrame(focusSidebar);')
        ->toContain('[data-sidebar-close], [data-sidebar-backdrop]')
        ->toContain("if (event.key === 'Escape')")
        ->toContain('lastSidebarOpener.focus({ preventScroll: true });')
        ->toContain("lastSidebarOpener = event.target.closest('[data-sidebar-open]');")
        ->toContain("syncSidebarAccessibility(element.dataset.open === 'true');")
        ->toContain("window.addEventListener('hashchange', syncSectionTabs);")
        ->toContain('function syncSectionTabs()')
        ->toContain("window.location.hash || tabs[0]?.getAttribute('href')")
        ->toContain("tab.toggleAttribute('aria-current', active);");
});
