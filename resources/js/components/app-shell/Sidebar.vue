<script setup lang="ts">
import { isNavigationItemActive } from '@/navigation'
import type { NavigationSection } from '@/types/navigation'

defineProps<{
    appName: string
    navigationLabel: string
    navigation: NavigationSection[]
    currentUrl: string
}>()
</script>

<template>
    <aside class="hidden w-64 shrink-0 border-e border-slate-200 bg-white lg:flex lg:flex-col">
        <div class="flex h-16 items-center border-b border-slate-200 px-6">
            <a href="/" class="text-base font-bold tracking-tight text-slate-950 focus-visible:outline-none">
                {{ appName }}
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-6" :aria-label="navigationLabel">
            <div v-for="section in navigation" :key="section.key" class="mb-7 last:mb-0">
                <p class="px-3 text-xs font-bold uppercase tracking-[0.14em] text-slate-400">
                    {{ section.label }}
                </p>
                <div class="mt-3 grid gap-1">
                    <template v-for="item in section.items" :key="item.key">
                        <a
                            v-if="item.href"
                            :href="item.href"
                            class="flex min-h-11 items-center rounded-lg px-3 text-sm font-semibold text-slate-600 transition-colors hover:bg-brand-50 hover:text-brand-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600"
                            :class="isNavigationItemActive(item, currentUrl) ? 'bg-brand-50 text-brand-700' : ''"
                            :aria-current="isNavigationItemActive(item, currentUrl) ? 'page' : undefined"
                        >
                            {{ item.label }}
                        </a>
                        <span
                            v-else
                            class="flex min-h-11 items-center rounded-lg px-3 text-sm font-semibold text-slate-400"
                            aria-disabled="true"
                        >
                            {{ item.label }}
                        </span>
                    </template>
                </div>
            </div>
        </nav>
    </aside>
</template>
