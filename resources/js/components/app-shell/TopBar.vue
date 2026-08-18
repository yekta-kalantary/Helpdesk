<script setup lang="ts">
import { Menu } from '@lucide/vue'

defineProps<{
    appName: string
    navigationLabel: string
    navigationOpen: boolean
}>()

const emit = defineEmits<{
    openNavigation: [trigger: HTMLButtonElement]
}>()

function openNavigation(event: MouseEvent): void {
    emit('openNavigation', event.currentTarget as HTMLButtonElement)
}
</script>

<template>
        <header class="flex h-14 items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-5">
        <div class="flex min-w-0 items-center gap-2.5">
            <button
                type="button"
                class="inline-flex size-10 items-center justify-center rounded-md text-slate-600 hover:bg-slate-100 hover:text-slate-950 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 lg:hidden"
                :aria-label="navigationLabel"
                aria-controls="mobile-navigation"
                aria-haspopup="dialog"
                :aria-expanded="navigationOpen"
                @click="openNavigation"
            >
                <Menu :size="20" aria-hidden="true" />
            </button>
            <a href="/" class="truncate text-sm font-semibold tracking-tight text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2 lg:hidden">
                {{ appName }}
            </a>
        </div>
        <slot />
    </header>
</template>
