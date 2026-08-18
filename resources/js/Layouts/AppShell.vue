<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

import { filterNavigationSections, getNavigationItemAriaCurrent } from '@/navigation'
import type { ApplicationShellProps } from '@/types/navigation'

const page = usePage<ApplicationShellProps>()

const navigation = computed(() =>
    filterNavigationSections(page.props.navigation, page.props.auth.capabilities),
)
</script>

<template>
    <div
        class="min-h-screen bg-slate-50 text-slate-950"
        :dir="page.props.direction"
        :lang="page.props.locale"
    >
        <header class="border-b border-slate-200 bg-white">
            <nav
                class="mx-auto flex max-w-7xl items-center gap-6 px-6 py-4"
                :aria-label="page.props.navigationLabel"
            >
                <a href="/" class="me-auto text-sm font-bold text-slate-950">
                    {{ page.props.appName }}
                </a>
                <div v-for="section in navigation" :key="section.key" class="flex items-center gap-4">
                    <span class="sr-only">{{ section.label }}</span>
                    <template v-for="item in section.items" :key="item.key">
                        <a
                            v-if="item.href"
                            :href="item.href"
                            class="text-sm font-semibold text-slate-600 hover:text-indigo-600"
                            :aria-current="getNavigationItemAriaCurrent(item, page.url)"
                        >
                            {{ item.label }}
                        </a>
                        <span
                            v-else
                            class="text-sm font-semibold text-slate-400"
                            aria-disabled="true"
                        >
                            {{ item.label }}
                        </span>
                    </template>
                </div>
                <span v-if="page.props.auth.user" class="text-sm text-slate-500">
                    {{ page.props.auth.user.name }}
                </span>
            </nav>
        </header>

        <main class="mx-auto max-w-7xl px-6 py-8">
            <slot />
        </main>
    </div>
</template>
