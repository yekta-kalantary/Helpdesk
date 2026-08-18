<script setup lang="ts">
import { Menu } from '@lucide/vue'

defineProps<{
    appName: string
    navigationLabel: string
    userName?: string
    navigationOpen: boolean
}>()

const emit = defineEmits<{
    openNavigation: []
}>()
</script>

<template>
    <header class="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-6">
        <div class="flex min-w-0 items-center gap-3">
            <button
                type="button"
                class="inline-flex size-11 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 hover:text-slate-950 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 lg:hidden"
                :aria-label="navigationLabel"
                aria-controls="mobile-navigation"
                aria-haspopup="dialog"
                :aria-expanded="navigationOpen"
                @click="emit('openNavigation')"
            >
                <Menu :size="20" aria-hidden="true" />
            </button>
            <a href="/" class="truncate text-base font-bold tracking-tight text-slate-950 lg:hidden">
                {{ appName }}
            </a>
        </div>
        <span v-if="userName" class="max-w-[50%] truncate text-sm font-medium text-slate-500">
            {{ userName }}
        </span>
    </header>
</template>
