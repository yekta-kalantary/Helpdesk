<script setup lang="ts">
import { Building2, FolderKanban, LayoutDashboard, ListChecks, Users } from '@lucide/vue'

import type { NavigationIconName } from '@/types/navigation'

const props = defineProps<{
    name: NavigationIconName
}>()

const iconMap = {
    building: Building2,
    dashboard: LayoutDashboard,
    projects: FolderKanban,
    tasks: ListChecks,
    users: Users,
} as const
</script>

<template>
    <component :is="iconMap[props.name]" :size="18" :stroke-width="1.8" aria-hidden="true" />
</template>
