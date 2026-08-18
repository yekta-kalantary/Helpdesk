<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { computed, nextTick, ref } from 'vue'

import MobileNavigation from '@/components/app-shell/MobileNavigation.vue'
import Sidebar from '@/components/app-shell/Sidebar.vue'
import TopBar from '@/components/app-shell/TopBar.vue'
import UserMenu from '@/components/app-shell/UserMenu.vue'
import { filterNavigationSections } from '@/navigation'
import type { ApplicationShellProps } from '@/types/navigation'

const page = usePage<ApplicationShellProps>()

const navigation = computed(() =>
    filterNavigationSections(page.props.navigation, page.props.auth.capabilities),
)

const mobileNavigationOpen = ref(false)
const mobileNavigationTrigger = ref<HTMLButtonElement | null>(null)

function openMobileNavigation(trigger: HTMLButtonElement): void {
    mobileNavigationTrigger.value = trigger
    mobileNavigationOpen.value = true
}

async function closeMobileNavigation(): Promise<void> {
    mobileNavigationOpen.value = false
    await nextTick()
    mobileNavigationTrigger.value?.focus()
}
</script>

<template>
    <div
        class="min-h-screen bg-slate-50 text-slate-950"
        :dir="page.props.direction"
        :lang="page.props.locale"
    >
        <div class="flex min-h-screen min-w-0">
            <Sidebar
                :app-name="page.props.appName"
                :navigation-label="page.props.navigationLabel"
                :navigation="navigation"
                :current-url="page.url"
            />
            <div class="flex min-w-0 flex-1 flex-col">
                <TopBar
                    :app-name="page.props.appName"
                    :navigation-label="page.props.navigationLabel"
                    :navigation-open="mobileNavigationOpen"
                    @open-navigation="openMobileNavigation"
                >
                        <UserMenu
                            v-if="page.props.auth.user"
                            :user="page.props.auth.user"
                            :labels="page.props.translations.app.userMenu"
                        />
                </TopBar>
                <main class="min-w-0 flex-1 px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
                    <slot />
                </main>
            </div>
        </div>
        <MobileNavigation
            :app-name="page.props.appName"
            :navigation-label="page.props.navigationLabel"
            :navigation-close-label="page.props.navigationCloseLabel"
            :navigation="navigation"
            :current-url="page.url"
            :open="mobileNavigationOpen"
            @close="closeMobileNavigation"
        />
    </div>
</template>
