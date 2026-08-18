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
    if (!item.href) {
        return false
    }

    const currentPath = new URL(url, 'http://localhost').pathname.replace(/\/$/, '') || '/'
    const itemPath = new URL(item.href, 'http://localhost').pathname.replace(/\/$/, '') || '/'

    return itemPath === '/' ? currentPath === '/' : currentPath === itemPath || currentPath.startsWith(`${itemPath}/`)
}

export function getNavigationItemAriaCurrent(item: NavigationItem, url: string): 'page' | undefined {
    return isNavigationItemActive(item, url) ? 'page' : undefined
}
