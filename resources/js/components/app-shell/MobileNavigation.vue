<script setup lang="ts">
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'

import { isNavigationItemActive } from '@/navigation'
import type { NavigationSection } from '@/types/navigation'

const props = defineProps<{
    appName: string
    navigationLabel: string
    navigationCloseLabel: string
    navigation: NavigationSection[]
    currentUrl: string
    open: boolean
}>()

const emit = defineEmits<{
    close: []
}>()

const closeButton = ref<HTMLButtonElement | null>(null)
const backdrop = ref<HTMLButtonElement | null>(null)
const drawer = ref<HTMLElement | null>(null)

function getFocusableElements(): HTMLElement[] {
    if (!backdrop.value && !drawer.value) {
        return []
    }

    const drawerElements = drawer.value
        ? Array.from(
              drawer.value.querySelectorAll<HTMLElement>(
                  'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])',
              ),
          )
        : []

    return backdrop.value ? [backdrop.value, ...drawerElements] : drawerElements
}

function closeOnEscape(event: KeyboardEvent): void {
    if (event.key === 'Escape' && props.open) {
        event.preventDefault()
        emit('close')
        return
    }

    if (event.key !== 'Tab' || !props.open) {
        return
    }

    const focusableElements = getFocusableElements()

    if (focusableElements.length === 0) {
        event.preventDefault()
        return
    }

    const firstElement = focusableElements[0]
    const lastElement = focusableElements[focusableElements.length - 1]

    if (event.shiftKey && document.activeElement === firstElement) {
        event.preventDefault()
        lastElement.focus()
    } else if (!event.shiftKey && document.activeElement === lastElement) {
        event.preventDefault()
        firstElement.focus()
    }
}

watch(
    () => props.open,
    async (open) => {
        if (open) {
            await nextTick()
            closeButton.value?.focus()
        }
    },
)

onMounted(() => document.addEventListener('keydown', closeOnEscape))
onBeforeUnmount(() => document.removeEventListener('keydown', closeOnEscape))
</script>

<template>
    <Transition
        enter-active-class="transition-opacity duration-200 ease-out motion-reduce:transition-none"
        enter-from-class="opacity-0"
        leave-active-class="transition-opacity duration-150 ease-in motion-reduce:transition-none"
        leave-to-class="opacity-0"
    >
        <div v-if="open" class="fixed inset-0 z-50 lg:hidden" role="presentation">
            <button
                ref="backdrop"
                type="button"
                class="absolute inset-0 h-full w-full bg-slate-950/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-white motion-reduce:transition-none"
                :aria-label="navigationCloseLabel"
                @click="emit('close')"
            />
            <Transition
                enter-active-class="transition-transform duration-200 ease-out motion-reduce:transition-none"
                enter-from-class="-translate-x-full rtl:translate-x-full"
                leave-active-class="transition-transform duration-150 ease-in motion-reduce:transition-none"
                leave-to-class="-translate-x-full rtl:translate-x-full"
            >
                <aside
                    v-if="open"
                    id="mobile-navigation"
                    ref="drawer"
                    class="relative flex h-full w-[min(18rem,calc(100vw-2rem))] max-w-full flex-col bg-white shadow-2xl"
                    role="dialog"
                    aria-modal="true"
                    :aria-label="navigationLabel"
                >
                    <div class="flex h-16 shrink-0 items-center justify-between border-b border-slate-200 px-5">
                        <a href="/" class="text-base font-bold tracking-tight text-slate-950 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2">
                            {{ appName }}
                        </a>
                        <button
                            ref="closeButton"
                            type="button"
                            class="inline-flex size-11 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 hover:text-slate-950 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600"
                            :aria-label="navigationCloseLabel"
                            @click="emit('close')"
                        >
                            <span aria-hidden="true" class="text-xl leading-none">&times;</span>
                        </button>
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
                                        @click="emit('close')"
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
            </Transition>
        </div>
    </Transition>
</template>
