<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { Eye, EyeOff, LockKeyhole } from '@lucide/vue'
import { computed, ref } from 'vue'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

interface ResetPasswordProps {
    auth: {
        user: null
        canLogin: boolean
        locale: string
        direction: 'rtl' | 'ltr'
    }
    reset: {
        email: string
        token: string
    }
}

const props = defineProps<ResetPasswordProps>()

const form = useForm({
    email: props.reset.email,
    password: '',
    password_confirmation: '',
})

const direction = computed(() => props.auth.direction)
const showPassword = ref(false)
const showConfirmation = ref(false)

function submit(): void {
    form.post(`/reset-password/${props.reset.token}`, {
        onFinish: () => form.reset('password', 'password_confirmation'),
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
                    {{ $page.props.translations?.identity?.passwordReset?.brand }}
                </p>
                <h1 class="text-3xl font-bold tracking-tight text-slate-950">
                    {{ $page.props.translations?.identity?.passwordReset?.title }}
                </h1>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    {{ $page.props.translations?.identity?.passwordReset?.description }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-8">
                <form class="space-y-5" @submit.prevent="submit">
                    <div v-if="form.errors.token" class="space-y-3 rounded-lg border border-red-200 bg-red-50 px-3 py-3 text-sm leading-6 text-red-700" role="alert">
                        <p>{{ form.errors.token }}</p>
                        <a href="/forgot-password" class="font-semibold underline underline-offset-4">
                            {{ $page.props.translations?.identity?.passwordReset?.request_new_link }}
                        </a>
                    </div>

                    <div class="space-y-2">
                        <label for="reset-email" class="text-sm font-semibold text-slate-800">
                            {{ $page.props.translations?.identity?.passwordReset?.email_label }}
                        </label>
                        <Input
                            id="reset-email"
                            v-model="form.email"
                            type="email"
                            name="email"
                            autocomplete="email"
                            required
                            :aria-invalid="Boolean(form.errors.email)"
                            :aria-describedby="form.errors.email ? 'reset-email-error' : undefined"
                            class="h-11"
                        />
                        <p v-if="form.errors.email" id="reset-email-error" class="text-sm text-red-700" role="alert">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label for="new-password" class="text-sm font-semibold text-slate-800">
                            {{ $page.props.translations?.identity?.passwordReset?.password_label }}
                        </label>
                        <div class="relative">
                            <LockKeyhole class="pointer-events-none absolute top-1/2 end-3 size-4 -translate-y-1/2 text-slate-400" aria-hidden="true" />
                            <Input
                                id="new-password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                autocomplete="new-password"
                                required
                                :aria-invalid="Boolean(form.errors.password)"
                                aria-describedby="password-requirements"
                                class="h-11 pe-10 ps-11"
                            />
                            <button
                                type="button"
                                class="absolute top-1/2 start-2 flex size-8 -translate-y-1/2 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                                :aria-label="showPassword ? $page.props.translations?.identity?.passwordReset?.hide_password : $page.props.translations?.identity?.passwordReset?.show_password"
                                :aria-pressed="showPassword"
                                @click="showPassword = !showPassword"
                            >
                                <EyeOff v-if="showPassword" class="size-4" aria-hidden="true" />
                                <Eye v-else class="size-4" aria-hidden="true" />
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="text-sm text-red-700" role="alert">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div id="password-requirements" class="rounded-lg bg-slate-50 px-3 py-3 text-sm leading-6 text-slate-600">
                        <p class="font-semibold text-slate-800">
                            {{ $page.props.translations?.identity?.passwordReset?.requirements_title }}
                        </p>
                        <ul class="mt-1 list-disc ps-5">
                            <li>{{ $page.props.translations?.identity?.passwordReset?.requirement_length }}</li>
                            <li>{{ $page.props.translations?.identity?.passwordReset?.requirement_confirmation }}</li>
                        </ul>
                    </div>

                    <div class="space-y-2">
                        <label for="password-confirmation" class="text-sm font-semibold text-slate-800">
                            {{ $page.props.translations?.identity?.passwordReset?.password_confirmation_label }}
                        </label>
                        <div class="relative">
                            <LockKeyhole class="pointer-events-none absolute top-1/2 end-3 size-4 -translate-y-1/2 text-slate-400" aria-hidden="true" />
                            <Input
                                id="password-confirmation"
                                v-model="form.password_confirmation"
                                :type="showConfirmation ? 'text' : 'password'"
                                name="password_confirmation"
                                autocomplete="new-password"
                                required
                                :aria-invalid="Boolean(form.errors.password)"
                                aria-describedby="password-requirements"
                                class="h-11 pe-10 ps-11"
                            />
                            <button
                                type="button"
                                class="absolute top-1/2 start-2 flex size-8 -translate-y-1/2 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                                :aria-label="showConfirmation ? $page.props.translations?.identity?.passwordReset?.hide_password : $page.props.translations?.identity?.passwordReset?.show_password"
                                :aria-pressed="showConfirmation"
                                @click="showConfirmation = !showConfirmation"
                            >
                                <EyeOff v-if="showConfirmation" class="size-4" aria-hidden="true" />
                                <Eye v-else class="size-4" aria-hidden="true" />
                            </button>
                        </div>
                    </div>

                    <Button type="submit" class="h-11 w-full bg-indigo-600 text-white hover:bg-indigo-700" :disabled="form.processing">
                        <span v-if="form.processing">{{ $page.props.translations?.identity?.passwordReset?.submitting }}</span>
                        <span v-else>{{ $page.props.translations?.identity?.passwordReset?.submit }}</span>
                    </Button>

                    <a
                        href="/login"
                        class="flex min-h-11 items-center justify-center text-sm font-semibold text-indigo-700 underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-indigo-600"
                    >
                        {{ $page.props.translations?.identity?.passwordReset?.return_to_login }}
                    </a>
                </form>
            </div>
        </section>
    </main>
</template>
