<script setup lang="ts">
defineProps<{
    appName?: string
}>()
</script>

<template>
    <main class="min-h-screen bg-slate-50 px-6 py-16 text-slate-950">
        <section class="mx-auto flex min-h-[60vh] max-w-5xl flex-col justify-center gap-6">
            <p class="text-sm font-semibold tracking-[0.2em] text-indigo-600 uppercase">
                Helpdesk
            </p>
            <h1 class="max-w-3xl text-4xl leading-tight font-bold tracking-tight sm:text-6xl">
                زیرساخت فرانت‌اند آماده است.
            </h1>
            <p class="max-w-2xl text-lg leading-8 text-slate-600">
                Inertia، Vue، TypeScript و سیستم توکن‌محور Tailwind برای ساخت پنل مدیریت پروژه آماده شده‌اند.
            </p>
        </section>
    </main>
</template>
