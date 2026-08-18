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
        class="relative flex min-h-screen items-center justify-center overflow-hidden bg-background px-4 py-8 text-foreground sm:px-6"
        :dir="direction"
    >
        <section class="relative w-full max-w-[25rem]">
            <div class="mb-6 text-center">
                <p class="mb-2 text-[11px] font-semibold tracking-[0.2em] text-teal-700 uppercase">
                    {{ $page.props.translations?.identity?.passwordRecovery?.brand }}
                </p>
                <h1 class="text-2xl font-semibold tracking-tight text-slate-900">
                    {{ $page.props.translations?.identity?.passwordRecovery?.title }}
                </h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    {{ $page.props.translations?.identity?.passwordRecovery?.description }}
                </p>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
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
                        class="flex min-h-10 items-center justify-center text-sm font-medium text-teal-800 underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-teal-700"
                    >
                        {{ $page.props.translations?.identity?.passwordRecovery?.return_to_login }}
                    </a>
                </div>

                <form v-else class="space-y-4" @submit.prevent="submit">
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
                                class="pe-10"
                            />
                        </div>
                        <p v-if="form.errors.email" id="recovery-email-error" class="text-sm text-red-700">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <Button type="submit" class="h-10 w-full bg-teal-700 text-white hover:bg-teal-800" :disabled="form.processing">
                        <span v-if="form.processing">{{ $page.props.translations?.identity?.passwordRecovery?.submitting }}</span>
                        <span v-else>{{ $page.props.translations?.identity?.passwordRecovery?.submit }}</span>
                    </Button>

                    <a
                        href="/login"
                        class="flex min-h-10 items-center justify-center text-sm font-medium text-teal-800 underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-teal-700"
                    >
                        {{ $page.props.translations?.identity?.passwordRecovery?.return_to_login }}
                    </a>
                </form>
            </div>
        </section>
    </main>
</template>
