import assert from 'node:assert/strict'
import test from 'node:test'

import {
    filterNavigationSections,
    getNavigationItemAriaCurrent,
    isNavigationItemActive,
} from './navigation.ts'

const sections = [
    {
        key: 'workspace',
        label: 'Workspace',
        items: [
            { key: 'dashboard', label: 'Dashboard', href: '/' },
            { key: 'users', label: 'Users', href: null, pending: true, capability: 'users.view' },
        ],
    },
]

test('filters restricted navigation items by capability without naming sections', () => {
    assert.deepEqual(filterNavigationSections(sections, []), [
        {
            key: 'workspace',
            label: 'Workspace',
            items: [{ key: 'dashboard', label: 'Dashboard', href: '/' }],
        },
    ])
    assert.deepEqual(filterNavigationSections(sections, ['users.view']), sections)
})

test('returns active state and aria-current only for the matching navigable item', () => {
    const item = sections[0].items[0]
    const pendingItem = sections[0].items[1]

    assert.equal(isNavigationItemActive(item, '/'), true)
    assert.equal(isNavigationItemActive(item, '/projects'), false)
    assert.equal(isNavigationItemActive(pendingItem, '/users'), false)
    assert.equal(getNavigationItemAriaCurrent(item, '/'), 'page')
    assert.equal(getNavigationItemAriaCurrent(item, '/projects'), undefined)
    assert.equal(getNavigationItemAriaCurrent(pendingItem, '/users'), undefined)
})
