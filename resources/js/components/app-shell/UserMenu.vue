<script setup lang="ts">
import { ChevronDown, LogOut, UserRound } from '@lucide/vue'
import { Link, router } from '@inertiajs/vue3'
import { nextTick, ref } from 'vue'

import type { AuthenticatedUser } from '@/types/navigation'

defineProps<{
    user: AuthenticatedUser
    labels: {
        open: string
        close: string
        profile: string
        logout: string
    }
}>()

const open = ref(false)
const trigger = ref<HTMLButtonElement | null>(null)
const menuId = 'user-menu-panel'
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
            class="inline-flex min-h-10 max-w-[min(18rem,60vw)] items-center gap-2 rounded-md px-2 text-start hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600"
            :aria-label="open ? labels.close : labels.open"
            :aria-controls="menuId"
            :aria-expanded="open"
            @click="open = !open"
            @keydown.esc="closeMenu"
        >
            <span class="truncate text-sm font-medium text-slate-700">{{ user.name }}</span>
            <ChevronDown :size="16" aria-hidden="true" />
        </button>

        <div
            v-if="open"
            :id="menuId"
            class="absolute end-0 top-full z-20 mt-1.5 w-56 rounded-lg border border-slate-200 bg-white p-1.5 shadow-lg"
            @keydown.esc="closeMenu"
        >
            <div class="px-2.5 py-1.5">
                <p class="truncate text-sm font-semibold text-slate-900">{{ user.name }}</p>
                <p class="truncate text-xs text-slate-500">{{ user.email }}</p>
            </div>
            <div class="my-1 border-t border-slate-100" />
            <Link
                href="/profile"
                class="flex min-h-10 w-full items-center gap-2 rounded-md px-2.5 text-[13px] text-slate-700 hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600"
                @click="open = false"
            >
                <UserRound :size="16" aria-hidden="true" />
                <span>{{ labels.profile }}</span>
            </Link>
            <button
                type="button"
                class="flex min-h-10 w-full items-center gap-2 rounded-md px-2.5 text-[13px] text-slate-700 hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600"
                @click="logout"
            >
                <LogOut :size="16" aria-hidden="true" />
                <span>{{ labels.logout }}</span>
            </button>
        </div>
    </div>
</template>
