<script setup lang="ts">
import type { ApplicationShellProps, Direction } from '@/types/navigation'

defineProps<{
    navigation?: ApplicationShellProps['navigation']
    direction?: Direction
}>()
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-950" :dir="direction ?? 'ltr'">
        <header class="border-b border-slate-200 bg-white">
            <nav class="mx-auto flex max-w-7xl items-center gap-6 px-6 py-4">
                <a
                    v-for="item in navigation ?? []"
                    :key="item.href"
                    :href="item.href"
                    class="text-sm font-semibold text-slate-600 hover:text-indigo-600"
                >
                    {{ item.label }}
                </a>
            </nav>
        </header>

        <main class="mx-auto max-w-7xl px-6 py-8">
            <slot />
        </main>
    </div>
</template>
