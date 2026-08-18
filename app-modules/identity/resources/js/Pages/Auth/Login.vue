<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { LockKeyhole, Mail } from '@lucide/vue'
import { computed } from 'vue'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

interface LoginProps {
    auth: {
        user: null
        canResetPassword: boolean
        canRememberSession: boolean
        locale: string
        direction: 'rtl' | 'ltr'
    }
}

const props = defineProps<LoginProps>()

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const direction = computed(() => props.auth.direction)

function submit(): void {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <main
        class="relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-50 px-4 py-10 text-slate-950 sm:px-6"
        :dir="direction"
    >
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute -top-32 start-1/2 h-80 w-80 -translate-x-1/2 rounded-full bg-indigo-100/70 blur-3xl" />
            <div class="absolute -bottom-40 -start-24 h-80 w-80 rounded-full bg-slate-200/80 blur-3xl" />
        </div>

        <section class="relative w-full max-w-[26rem]">
            <div class="mb-8 text-center">
                <p class="mb-3 text-xs font-bold tracking-[0.24em] text-indigo-600 uppercase">
                    {{ $page.props.translations?.identity?.login?.brand }}
                </p>
                <h1 class="text-3xl font-bold tracking-tight text-slate-950">
                    {{ $page.props.translations?.identity?.login?.title }}
                </h1>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    {{ $page.props.translations?.identity?.login?.description }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-8">
                <form class="space-y-5" @submit.prevent="submit">
                    <div
                        v-if="form.errors.email"
                        class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm leading-6 text-red-700"
                        role="alert"
                    >
                        {{ form.errors.email }}
                    </div>

                    <div class="space-y-2">
                        <label for="email" class="text-sm font-semibold text-slate-800">
                            {{ $page.props.translations?.identity?.login?.email_label }}
                        </label>
                        <div class="relative">
                            <Mail
                                class="pointer-events-none absolute top-1/2 end-3 size-4 -translate-y-1/2 text-slate-400"
                                aria-hidden="true"
                            />
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                name="email"
                                autocomplete="username"
                                required
                                :aria-invalid="Boolean(form.errors.email)"
                                :aria-describedby="form.errors.email ? 'email-error' : undefined"
                                class="h-11 pe-10"
                            />
                        </div>
                        <p v-if="form.errors.email" id="email-error" class="text-sm text-red-700">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label for="password" class="text-sm font-semibold text-slate-800">
                            {{ $page.props.translations?.identity?.login?.password_label }}
                        </label>
                        <div class="relative">
                            <LockKeyhole
                                class="pointer-events-none absolute top-1/2 end-3 size-4 -translate-y-1/2 text-slate-400"
                                aria-hidden="true"
                            />
                            <Input
                                id="password"
                                v-model="form.password"
                                type="password"
                                name="password"
                                autocomplete="current-password"
                                required
                                :aria-invalid="Boolean(form.errors.password)"
                                :aria-describedby="form.errors.password ? 'password-error' : undefined"
                                class="h-11 pe-10"
                            />
                        </div>
                        <p v-if="form.errors.password" id="password-error" class="text-sm text-red-700">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <label v-if="auth.canRememberSession" class="flex min-h-11 items-center gap-3 text-sm text-slate-600">
                        <input v-model="form.remember" type="checkbox" name="remember" class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                        <span>{{ $page.props.translations?.identity?.login?.remember_label }}</span>
                    </label>

                    <Button type="submit" class="h-11 w-full bg-indigo-600 text-white hover:bg-indigo-700" :disabled="form.processing">
                        <span v-if="form.processing">{{ $page.props.translations?.identity?.login?.submitting }}</span>
                        <span v-else>{{ $page.props.translations?.identity?.login?.submit }}</span>
                    </Button>

                    <a
                        v-if="auth.canResetPassword"
                        href="/forgot-password"
                        class="block min-h-11 pt-2 text-center text-sm font-semibold text-indigo-700 underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-indigo-600"
                    >
                        {{ $page.props.translations?.identity?.login?.forgot_password }}
                    </a>
                </form>
            </div>
        </section>
    </main>
</template>
