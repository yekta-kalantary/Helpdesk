export type Direction = 'ltr' | 'rtl'

export interface AuthenticatedUser {
    id: number | string
    name: string
    email: string
}

export interface NavigationItem {
    label: string
    href: string
    capability?: string
}

export interface ApplicationShellProps {
    [key: string]: unknown
    appName: string
    locale: string
    direction: Direction
    auth: {
        user: AuthenticatedUser | null
        capabilities: string[]
    }
    navigation: NavigationItem[]
}
