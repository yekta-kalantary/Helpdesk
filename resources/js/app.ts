import '../css/app.css'

import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createApp, h, type DefineComponent } from 'vue'

const pages = {
    ...import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
    ...import.meta.glob<DefineComponent>('../../app-modules/**/resources/js/Pages/**/*.vue'),
}

function resolvePagePath(name: string): string {
    const rootPath = `./Pages/${name}.vue`

    if (rootPath in pages) {
        return rootPath
    }

    const [module, ...segments] = name.split('/')
    const modulePath = `../../app-modules/${module.toLowerCase()}/resources/js/Pages/${segments.join('/')}.vue`

    if (modulePath in pages) {
        return modulePath
    }

    throw new Error(`Inertia page [${name}] was not found.`)
}

createInertiaApp({
    title: (title) => title ?? '',
    resolve: (name) =>
        resolvePageComponent<DefineComponent>(
            resolvePagePath(name),
            pages,
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el)
    },
})
