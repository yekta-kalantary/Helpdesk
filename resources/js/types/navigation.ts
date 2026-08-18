export type Direction = 'ltr' | 'rtl'

export interface AuthenticatedUser {
    id: number | string
    name: string
    email: string
}

export interface NavigationItem {
    key: string
    label: string
    href: string | null
    capability?: string
    pending?: boolean
}

export interface NavigationSection {
    key: string
    label: string
    items: NavigationItem[]
}

export interface ApplicationShellProps {
    [key: string]: unknown
    appName: string
    locale: string
    direction: Direction
    navigationLabel: string
    auth: {
        user: AuthenticatedUser | null
        capabilities: string[]
    }
    navigation: NavigationSection[]
}
