<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { Mail } from '@lucide/vue'
import { computed } from 'vue'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

interface ForgotPasswordProps {
    auth: {
        user: null
        canLogin: boolean
        locale: string
        direction: 'rtl' | 'ltr'
    }
    status?: string
}

const props = defineProps<ForgotPasswordProps>()

const form = useForm({
    email: '',
})

const direction = computed(() => props.auth.direction)

function submit(): void {
    form.post('/forgot-password', {
        preserveScroll: true,
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
                    {{ $page.props.translations?.identity?.passwordRecovery?.brand }}
                </p>
                <h1 class="text-3xl font-bold tracking-tight text-slate-950">
                    {{ $page.props.translations?.identity?.passwordRecovery?.title }}
                </h1>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    {{ $page.props.translations?.identity?.passwordRecovery?.description }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-8">
                <div v-if="status" class="space-y-4" role="status" aria-live="polite" tabindex="-1">
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm leading-6 text-emerald-800">
                        <h2 class="font-semibold">
                            {{ $page.props.translations?.identity?.passwordRecovery?.confirmation_title }}
                        </h2>
                        <p class="mt-1">{{ status }}</p>
                        <p class="mt-2 text-emerald-700">
                            {{ $page.props.translations?.identity?.passwordRecovery?.check_spam }}
                        </p>
                    </div>
                    <a
                        href="/login"
                        class="flex min-h-11 items-center justify-center text-sm font-semibold text-indigo-700 underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-indigo-600"
                    >
                        {{ $page.props.translations?.identity?.passwordRecovery?.return_to_login }}
                    </a>
                </div>

                <form v-else class="space-y-5" @submit.prevent="submit">
                    <div v-if="form.errors.email" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm leading-6 text-red-700" role="alert">
                        {{ form.errors.email }}
                    </div>

                    <div class="space-y-2">
                        <label for="recovery-email" class="text-sm font-semibold text-slate-800">
                            {{ $page.props.translations?.identity?.passwordRecovery?.email_label }}
                        </label>
                        <div class="relative">
                            <Mail class="pointer-events-none absolute top-1/2 end-3 size-4 -translate-y-1/2 text-slate-400" aria-hidden="true" />
                            <Input
                                id="recovery-email"
                                v-model="form.email"
                                type="email"
                                name="email"
                                autocomplete="email"
                                required
                                :aria-invalid="Boolean(form.errors.email)"
                                :aria-describedby="form.errors.email ? 'recovery-email-error' : undefined"
                                class="h-11 pe-10"
                            />
                        </div>
                        <p v-if="form.errors.email" id="recovery-email-error" class="text-sm text-red-700">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <Button type="submit" class="h-11 w-full bg-indigo-600 text-white hover:bg-indigo-700" :disabled="form.processing">
                        <span v-if="form.processing">{{ $page.props.translations?.identity?.passwordRecovery?.submitting }}</span>
                        <span v-else>{{ $page.props.translations?.identity?.passwordRecovery?.submit }}</span>
                    </Button>

                    <a
                        href="/login"
                        class="flex min-h-11 items-center justify-center text-sm font-semibold text-indigo-700 underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-indigo-600"
                    >
                        {{ $page.props.translations?.identity?.passwordRecovery?.return_to_login }}
                    </a>
                </form>
            </div>
        </section>
    </main>
</template>
