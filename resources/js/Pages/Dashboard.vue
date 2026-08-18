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
        <h1 id="dashboard-title" class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">
            {{ title }}
        </h1>
        <p class="mt-1.5 max-w-2xl text-sm leading-6 text-slate-600">
            {{ summary }}
        </p>
    </section>
</template>
