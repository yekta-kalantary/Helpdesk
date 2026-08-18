<script setup lang="ts">
import { isNavigationItemActive } from '@/navigation'
import NavigationIcon from '@/components/app-shell/NavigationIcon.vue'
import type { NavigationSection } from '@/types/navigation'

defineProps<{
    appName: string
    navigationLabel: string
    navigation: NavigationSection[]
    currentUrl: string
}>()
</script>

<template>
    <aside class="hidden w-56 shrink-0 border-e border-slate-200 bg-white lg:flex lg:flex-col">
        <div class="flex h-14 items-center border-b border-slate-200 px-5">
            <a href="/" class="text-sm font-semibold tracking-tight text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2">
                {{ appName }}
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto px-2.5 py-5" :aria-label="navigationLabel">
            <div v-for="section in navigation" :key="section.key" class="mb-5 last:mb-0">
                <p class="px-2.5 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">
                    {{ section.label }}
                </p>
                <div class="mt-2 grid gap-0.5">
                    <template v-for="item in section.items" :key="item.key">
                        <a
                            v-if="item.href"
                            :href="item.href"
                            class="flex min-h-10 items-center gap-2.5 rounded-md border-s-2 border-transparent px-2.5 text-[13px] font-medium text-slate-600 transition-colors hover:bg-teal-50 hover:text-teal-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600"
                            :class="isNavigationItemActive(item, currentUrl) ? 'border-teal-700 bg-teal-50 text-teal-800' : ''"
                            :aria-current="isNavigationItemActive(item, currentUrl) ? 'page' : undefined"
                        >
                            <NavigationIcon :name="item.icon" class="shrink-0" />
                            {{ item.label }}
                        </a>
                        <span
                            v-else
                            class="flex min-h-10 items-center gap-2.5 rounded-md border-s-2 border-transparent px-2.5 text-[13px] font-medium text-slate-400"
                            aria-disabled="true"
                        >
                            <NavigationIcon :name="item.icon" class="shrink-0" />
                            {{ item.label }}
                        </span>
                    </template>
                </div>
            </div>
        </nav>
    </aside>
</template>
