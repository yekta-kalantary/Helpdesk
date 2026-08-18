<script setup lang="ts">
defineProps<{
    appName?: string
}>()
</script>

<template>
    <main class="min-h-screen bg-background px-5 py-12 text-foreground sm:px-6">
        <section class="mx-auto flex min-h-[60vh] max-w-5xl flex-col justify-center gap-4">
            <p class="text-xs font-semibold tracking-[0.18em] text-teal-700 uppercase">
                Helpdesk
            </p>
            <h1 class="max-w-3xl text-3xl leading-tight font-semibold tracking-tight sm:text-5xl">
                زیرساخت فرانت‌اند آماده است.
            </h1>
            <p class="max-w-2xl text-base leading-7 text-slate-600">
                Inertia، Vue، TypeScript و سیستم توکن‌محور Tailwind برای ساخت پنل مدیریت پروژه آماده شده‌اند.
            </p>
        </section>
    </main>
</template>
