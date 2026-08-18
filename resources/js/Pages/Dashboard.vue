<script setup lang="ts">
import AppShell from '@/Layouts/AppShell.vue'

defineOptions({
    layout: AppShell,
})

defineProps<{
    title: string
    summary: string
}>()
</script>

<template>
    <section aria-labelledby="dashboard-title" class="mx-auto w-full max-w-7xl">
        <h1 id="dashboard-title" class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">
            {{ title }}
        </h1>
        <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
            {{ summary }}
        </p>
    </section>
</template>
