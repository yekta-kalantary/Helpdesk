<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { Eye, EyeOff, LockKeyhole, Mail, Smartphone, UserRound } from '@lucide/vue'
import { computed, ref } from 'vue'

import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import AppShell from '@/Layouts/AppShell.vue'

defineOptions({
    layout: AppShell,
})

interface ProfileProps {
    direction: 'rtl' | 'ltr'
    profile: {
        user: {
            id: number | string
            name: string
            last_name: string
            email: string
            mobile: string | null
        }
        status?: {
            personal?: string
            contact?: string
            email?: string
            mobile?: string
            password?: string
        }
    }
}

const props = defineProps<ProfileProps>()

const personalForm = useForm({
    name: props.profile.user.name,
    last_name: props.profile.user.last_name,
})
const emailForm = useForm({
    email: props.profile.user.email,
})
const mobileForm = useForm({
    mobile: props.profile.user.mobile ?? '',
})
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const personalSaved = ref(false)
const emailSaved = ref(false)
const mobileSaved = ref(false)
const passwordSaved = ref(false)
const showCurrentPassword = ref(false)
const showPassword = ref(false)
const showConfirmation = ref(false)
const direction = computed(() => props.direction)
const personalStatus = computed(() => props.profile.status?.personal)
const emailStatus = computed(() => props.profile.status?.email)
const mobileStatus = computed(() => props.profile.status?.mobile)
const passwordStatus = computed(() => props.profile.status?.password)

function submitPersonal(): void {
    personalSaved.value = false
    personalForm.post('/profile/personal', {
        preserveScroll: true,
        onSuccess: () => {
            personalSaved.value = true
        },
    })
}

function submitEmail(): void {
    emailSaved.value = false
    emailForm.post('/profile/email', {
        preserveScroll: true,
        onSuccess: () => {
            emailSaved.value = true
        },
    })
}

function submitMobile(): void {
    mobileSaved.value = false
    mobileForm.post('/profile/mobile', {
        preserveScroll: true,
        onSuccess: () => {
            mobileSaved.value = true
        },
    })
}

function submitPassword(): void {
    passwordSaved.value = false
    passwordForm.post('/profile/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordSaved.value = true
            passwordForm.reset()
        },
    })
}
</script>

<template>
    <section class="mx-auto w-full max-w-5xl" :dir="direction" aria-labelledby="profile-title">
        <header class="mb-6 max-w-2xl">
            <h1 id="profile-title" class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">
                {{ $page.props.translations?.identity?.profile?.title }}
            </h1>
            <p class="mt-1.5 text-sm leading-6 text-slate-600">
                {{ $page.props.translations?.identity?.profile?.description }}
            </p>
        </header>

        <div class="columns-1 gap-4 lg:columns-2">
            <Card class="mb-4 break-inside-avoid">
                <div class="p-5 sm:p-6">
                <div class="mb-5 flex items-start gap-3">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-md bg-teal-50 text-teal-800">
                        <UserRound class="size-4" aria-hidden="true" />
                    </div>
                    <div>
                        <CardTitle>{{ $page.props.translations?.identity?.profile?.personal?.title }}</CardTitle>
                        <CardDescription class="mt-0.5">{{ $page.props.translations?.identity?.profile?.personal?.description }}</CardDescription>
                    </div>
                </div>
                <form class="space-y-5" @submit.prevent="submitPersonal">
                    <div class="grid gap-6">
                        <div class="flex flex-col gap-3">
                            <label for="profile-name" class="text-sm font-semibold text-slate-800">{{ $page.props.translations?.identity?.profile?.personal?.name_label }}</label>
                            <Input id="profile-name" v-model="personalForm.name" name="name" autocomplete="given-name" required :aria-invalid="Boolean(personalForm.errors.name)" :aria-describedby="personalForm.errors.name ? 'profile-name-error' : undefined" />
                            <p v-if="personalForm.errors.name" id="profile-name-error" class="text-sm text-red-700" role="alert">{{ personalForm.errors.name }}</p>
                        </div>
                        <div class="flex flex-col gap-3">
                            <label for="profile-last-name" class="text-sm font-semibold text-slate-800">{{ $page.props.translations?.identity?.profile?.personal?.last_name_label }}</label>
                            <Input id="profile-last-name" v-model="personalForm.last_name" name="last_name" autocomplete="family-name" required :aria-invalid="Boolean(personalForm.errors.last_name)" :aria-describedby="personalForm.errors.last_name ? 'profile-last-name-error' : undefined" />
                            <p v-if="personalForm.errors.last_name" id="profile-last-name-error" class="text-sm text-red-700" role="alert">{{ personalForm.errors.last_name }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <Button type="submit" class="min-h-10 bg-teal-700 text-white hover:bg-teal-800" :disabled="personalForm.processing">
                            {{ personalForm.processing ? $page.props.translations?.identity?.profile?.personal?.submitting : $page.props.translations?.identity?.profile?.personal?.submit }}
                        </Button>
                        <p v-if="personalSaved || personalStatus" class="text-sm font-medium text-emerald-700" role="status" aria-live="polite">{{ personalStatus || $page.props.translations?.identity?.profile?.personal?.saved }}</p>
                    </div>
                </form>
                </div>
            </Card>

            <Card class="mb-4 break-inside-avoid">
                <div class="p-5 sm:p-6">
                    <div class="mb-5 flex items-start gap-3">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-md bg-teal-50 text-teal-800">
                            <Mail class="size-4" aria-hidden="true" />
                        </div>
                        <div>
                            <CardTitle>{{ $page.props.translations?.identity?.profile?.contact?.email_label }}</CardTitle>
                            <CardDescription class="mt-0.5">{{ $page.props.translations?.identity?.profile?.contact?.description }}</CardDescription>
                        </div>
                    </div>
                    <form class="space-y-5" @submit.prevent="submitEmail">
                        <div class="flex flex-col gap-3">
                            <label for="profile-email" class="text-sm font-semibold text-slate-800">{{ $page.props.translations?.identity?.profile?.contact?.email_label }}</label>
                            <Input id="profile-email" v-model="emailForm.email" type="email" name="email" autocomplete="email" required dir="ltr" class="text-left" :aria-invalid="Boolean(emailForm.errors.email)" :aria-describedby="emailForm.errors.email ? 'profile-email-error' : undefined" />
                            <p v-if="emailForm.errors.email" id="profile-email-error" class="text-sm text-red-700" role="alert">{{ emailForm.errors.email }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <Button type="submit" class="min-h-10 bg-teal-700 text-white hover:bg-teal-800" :disabled="emailForm.processing">
                                {{ emailForm.processing ? $page.props.translations?.identity?.profile?.contact?.email_submitting : $page.props.translations?.identity?.profile?.contact?.email_submit }}
                            </Button>
                            <p v-if="emailSaved || emailStatus" class="text-sm font-medium text-emerald-700" role="status" aria-live="polite">{{ emailStatus || $page.props.translations?.identity?.profile?.contact?.email_saved }}</p>
                        </div>
                    </form>
                </div>
            </Card>

            <Card class="mb-4 break-inside-avoid">
                <div class="p-5 sm:p-6">
                <div class="mb-5 flex items-start gap-3">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-md bg-teal-50 text-teal-800">
                        <Smartphone class="size-4" aria-hidden="true" />
                    </div>
                    <div>
                        <CardTitle>{{ $page.props.translations?.identity?.profile?.contact?.mobile_label }}</CardTitle>
                        <CardDescription class="mt-0.5">{{ $page.props.translations?.identity?.profile?.contact?.description }}</CardDescription>
                    </div>
                </div>
                <form class="space-y-5" @submit.prevent="submitMobile">
                    <div class="flex flex-col gap-3">
                        <label for="profile-mobile" class="text-sm font-semibold text-slate-800">{{ $page.props.translations?.identity?.profile?.contact?.mobile_label }}</label>
                        <Input id="profile-mobile" v-model="mobileForm.mobile" type="tel" name="mobile" autocomplete="tel" dir="ltr" class="text-left" :aria-invalid="Boolean(mobileForm.errors.mobile)" :aria-describedby="mobileForm.errors.mobile ? 'profile-mobile-error' : undefined" />
                        <p v-if="mobileForm.errors.mobile" id="profile-mobile-error" class="text-sm text-red-700" role="alert">{{ mobileForm.errors.mobile }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <Button type="submit" class="min-h-10 bg-teal-700 text-white hover:bg-teal-800" :disabled="mobileForm.processing">
                            {{ mobileForm.processing ? $page.props.translations?.identity?.profile?.contact?.mobile_submitting : $page.props.translations?.identity?.profile?.contact?.mobile_submit }}
                        </Button>
                        <p v-if="mobileSaved || mobileStatus" class="text-sm font-medium text-emerald-700" role="status" aria-live="polite">{{ mobileStatus || $page.props.translations?.identity?.profile?.contact?.mobile_saved }}</p>
                    </div>
                </form>
                </div>
            </Card>

            <Card class="mb-4 break-inside-avoid">
                <div class="p-5 sm:p-6">
                <div class="mb-5 flex items-start gap-3">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-md bg-teal-50 text-teal-800">
                        <LockKeyhole class="size-4" aria-hidden="true" />
                    </div>
                    <div>
                        <CardTitle>{{ $page.props.translations?.identity?.profile?.password?.title }}</CardTitle>
                        <CardDescription class="mt-0.5">{{ $page.props.translations?.identity?.profile?.password?.description }}</CardDescription>
                    </div>
                </div>
                <form class="space-y-5" @submit.prevent="submitPassword">
                    <div class="grid grid-cols-1 gap-6">
                        <div class="flex flex-col gap-3">
                            <label for="current-password" class="text-sm font-semibold text-slate-800">{{ $page.props.translations?.identity?.profile?.password?.current_label }}</label>
                            <div dir="ltr" class="relative">
                                <Input id="current-password" v-model="passwordForm.current_password" :type="showCurrentPassword ? 'text' : 'password'" name="current_password" autocomplete="current-password" required dir="ltr" :aria-invalid="Boolean(passwordForm.errors.current_password)" :aria-describedby="passwordForm.errors.current_password ? 'current-password-error' : undefined" class="pe-11 text-left" />
                                <button type="button" class="absolute top-1/2 end-2 flex size-11 -translate-y-1/2 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700" :aria-label="showCurrentPassword ? $page.props.translations?.identity?.profile?.password?.hide_password : $page.props.translations?.identity?.profile?.password?.show_password" :aria-pressed="showCurrentPassword" @click="showCurrentPassword = !showCurrentPassword">
                                    <EyeOff v-if="showCurrentPassword" class="size-4" aria-hidden="true" />
                                    <Eye v-else class="size-4" aria-hidden="true" />
                                </button>
                            </div>
                            <p v-if="passwordForm.errors.current_password" id="current-password-error" class="text-sm text-red-700" role="alert">{{ passwordForm.errors.current_password }}</p>
                        </div>
                        <div class="flex flex-col gap-3">
                            <label for="profile-password" class="text-sm font-semibold text-slate-800">{{ $page.props.translations?.identity?.profile?.password?.new_label }}</label>
                            <div dir="ltr" class="relative">
                                <Input id="profile-password" v-model="passwordForm.password" :type="showPassword ? 'text' : 'password'" name="password" autocomplete="new-password" required dir="ltr" aria-describedby="password-requirements profile-password-error" :aria-invalid="Boolean(passwordForm.errors.password)" class="pe-11 text-left" />
                                <button type="button" class="absolute top-1/2 end-2 flex size-11 -translate-y-1/2 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700" :aria-label="showPassword ? $page.props.translations?.identity?.profile?.password?.hide_password : $page.props.translations?.identity?.profile?.password?.show_password" :aria-pressed="showPassword" @click="showPassword = !showPassword">
                                    <EyeOff v-if="showPassword" class="size-4" aria-hidden="true" />
                                    <Eye v-else class="size-4" aria-hidden="true" />
                                </button>
                            </div>
                            <p v-if="passwordForm.errors.password" id="profile-password-error" class="text-sm text-red-700" role="alert">{{ passwordForm.errors.password }}</p>
                        </div>
                        <div class="flex flex-col gap-3">
                            <label for="profile-password-confirmation" class="text-sm font-semibold text-slate-800">{{ $page.props.translations?.identity?.profile?.password?.confirmation_label }}</label>
                            <div dir="ltr" class="relative">
                                <Input id="profile-password-confirmation" v-model="passwordForm.password_confirmation" :type="showConfirmation ? 'text' : 'password'" name="password_confirmation" autocomplete="new-password" required dir="ltr" aria-describedby="password-requirements profile-password-confirmation-error" :aria-invalid="Boolean(passwordForm.errors.password)" class="pe-11 text-left" />
                                <button type="button" class="absolute top-1/2 end-2 flex size-11 -translate-y-1/2 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700" :aria-label="showConfirmation ? $page.props.translations?.identity?.profile?.password?.hide_password : $page.props.translations?.identity?.profile?.password?.show_password" :aria-pressed="showConfirmation" @click="showConfirmation = !showConfirmation">
                                    <EyeOff v-if="showConfirmation" class="size-4" aria-hidden="true" />
                                    <Eye v-else class="size-4" aria-hidden="true" />
                                </button>
                            </div>
                            <p v-if="passwordForm.errors.password" id="profile-password-confirmation-error" class="text-sm text-red-700" role="alert">{{ passwordForm.errors.password }}</p>
                        </div>
                    </div>
                    <p id="password-requirements" class="text-sm leading-6 text-slate-600">{{ $page.props.translations?.identity?.profile?.password?.requirements }}</p>
                    <div class="flex flex-wrap items-center gap-3">
                        <Button type="submit" class="min-h-10 bg-teal-700 text-white hover:bg-teal-800" :disabled="passwordForm.processing">
                            {{ passwordForm.processing ? $page.props.translations?.identity?.profile?.password?.submitting : $page.props.translations?.identity?.profile?.password?.submit }}
                        </Button>
                        <p v-if="passwordSaved || passwordStatus" class="text-sm font-medium text-emerald-700" role="status" aria-live="polite">{{ passwordStatus || $page.props.translations?.identity?.profile?.password?.saved }}</p>
                    </div>
                </form>
            </div>
            </Card>
        </div>
    </section>
</template>
