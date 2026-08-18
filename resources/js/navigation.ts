import type { NavigationItem, NavigationSection } from '@/types/navigation'

export function filterNavigationSections(
    sections: NavigationSection[],
    capabilities: string[],
): NavigationSection[] {
    return sections
        .map((section) => ({
            ...section,
            items: section.items.filter(
                (item) => !item.capability || capabilities.includes(item.capability),
            ),
        }))
        .filter((section) => section.items.length > 0)
}

export function isNavigationItemActive(item: NavigationItem, url: string): boolean {
    const currentPath = new URL(url, window.location.origin).pathname.replace(/\/$/, '') || '/'
    const itemPath = new URL(item.href, window.location.origin).pathname.replace(/\/$/, '') || '/'

    return itemPath === '/' ? currentPath === '/' : currentPath === itemPath || currentPath.startsWith(`${itemPath}/`)
}
