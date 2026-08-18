<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

import type { ApplicationShellProps } from '@/types/navigation'

const page = usePage<ApplicationShellProps>()

const navigation = computed(() =>
    page.props.navigation.filter(
        (item) => !item.capability || page.props.auth.capabilities.includes(item.capability),
    ),
)
</script>

<template>
    <div
        class="min-h-screen bg-slate-50 text-slate-950"
        :dir="page.props.direction"
        :lang="page.props.locale"
    >
        <header class="border-b border-slate-200 bg-white">
            <nav class="mx-auto flex max-w-7xl items-center gap-6 px-6 py-4">
                <a href="/" class="me-auto text-sm font-bold text-slate-950">
                    {{ page.props.appName }}
                </a>
                <a
                    v-for="item in navigation"
                    :key="item.href"
                    :href="item.href"
                    class="text-sm font-semibold text-slate-600 hover:text-indigo-600"
                >
                    {{ item.label }}
                </a>
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
