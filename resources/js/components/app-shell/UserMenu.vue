<script setup lang="ts">
import { ChevronDown, Globe, LogOut } from '@lucide/vue'
import { router } from '@inertiajs/vue3'
import { computed, nextTick, ref } from 'vue'

import type { AuthenticatedUser } from '@/types/navigation'

const props = defineProps<{
    user: AuthenticatedUser
    locale: string
    labels: {
        open: string
        close: string
        logout: string
        switchLocale: string
        english: string
        persian: string
    }
    currentPath: string
}>()

const open = ref(false)
const trigger = ref<HTMLButtonElement | null>(null)
const menuId = 'user-menu-panel'
const nextLocale = computed(() => (props.locale === 'fa' ? 'en' : 'fa'))
const nextLocaleLabel = computed(() => (nextLocale.value === 'fa' ? props.labels.persian : props.labels.english))
const localeUrl = computed(() => `/locale/${nextLocale.value}?redirect=${encodeURIComponent(props.currentPath)}`)

function logout(): void {
    router.post('/logout')
}

async function closeMenu(): Promise<void> {
    open.value = false
    await nextTick()
    trigger.value?.focus()
}
</script>

<template>
    <div class="relative">
        <button
            ref="trigger"
            type="button"
            class="inline-flex min-h-11 max-w-[min(18rem,60vw)] items-center gap-2 rounded-lg px-2 text-start hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600"
            :aria-label="open ? labels.close : labels.open"
            :aria-controls="menuId"
            :aria-expanded="open"
            @click="open = !open"
        >
            <span class="truncate text-sm font-medium text-slate-700">{{ user.name }}</span>
            <ChevronDown :size="16" aria-hidden="true" />
        </button>

        <div
            v-if="open"
            :id="menuId"
            class="absolute end-0 top-full z-20 mt-2 w-56 rounded-xl border border-slate-200 bg-white p-2 shadow-xl"
            @keydown.esc="closeMenu"
        >
            <div class="px-3 py-2">
                <p class="truncate text-sm font-semibold text-slate-900">{{ user.name }}</p>
                <p class="truncate text-xs text-slate-500">{{ user.email }}</p>
            </div>
            <div class="my-1 border-t border-slate-100" />
            <a
                :href="localeUrl"
                class="flex min-h-11 items-center gap-2 rounded-lg px-3 text-sm text-slate-700 hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600"
                @click="closeMenu"
            >
                <Globe :size="16" aria-hidden="true" />
                <span>{{ labels.switchLocale }}: {{ nextLocaleLabel }}</span>
            </a>
            <button
                type="button"
                class="flex min-h-11 w-full items-center gap-2 rounded-lg px-3 text-sm text-slate-700 hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600"
                @click="logout"
            >
                <LogOut :size="16" aria-hidden="true" />
                <span>{{ labels.logout }}</span>
            </button>
        </div>
    </div>
</template>
