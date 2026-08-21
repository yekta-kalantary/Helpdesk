<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { Plus, UsersRound, X } from '@lucide/vue'
import { nextTick, onBeforeUnmount, ref, watch } from 'vue'

import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import AppShell from '@/Layouts/AppShell.vue'
import type { Direction } from '@/types/navigation'

defineOptions({ layout: AppShell })

interface User {
    id: number
    name: string
    last_name: string
    email: string
    mobile: string | null
    role: string
    client: { id: number; name: string } | null
    is_active: boolean
}

interface PaginationLink {
    url: string | null
    label: string
    active: boolean
}

interface UsersPage {
    data: User[]
    links: PaginationLink[]
}

interface UserTranslations {
    title: string
    description: string
    create: string
    close: string
    empty: string
    table: Record<string, string>
    active: string
    inactive: string
    not_available: string
    roles: Record<string, string>
    form: Record<string, string>
    validation: Record<string, string>
    pagination: string
}

const props = defineProps<{
    users: UsersPage
    clients: { id: number; name: string }[]
    roles: string[]
    direction: Direction
    translations: { identity: { users: UserTranslations } }
}>()

const copy = props.translations.identity.users
const isModalOpen = ref(false)
const createTrigger = ref<HTMLButtonElement | null>(null)
const dialog = ref<HTMLElement | null>(null)
const firstField = ref<HTMLInputElement | null>(null)

const form = useForm({
    name: '',
    last_name: '',
    email: '',
    mobile: '',
    role: '',
    client_id: null as number | null,
    is_active: true,
    password_mode: 'manual',
    password: '',
    password_confirmation: '',
})

function openModal(): void {
    isModalOpen.value = true
}

async function closeModal(): Promise<void> {
    if (form.processing) {
        return
    }

    isModalOpen.value = false
    await nextTick()
    createTrigger.value?.focus()
}

function submit(): void {
    form.post('/users', {
        preserveScroll: true,
        onSuccess: async () => {
            form.reset()
            form.is_active = true
            form.password_mode = 'manual'
            await closeModal()
        },
    })
}

function fieldError(field: string): string | undefined {
    const error = form.errors[field]

    return Array.isArray(error) ? error[0] : error
}

function onRoleChange(): void {
    if (form.role !== 'customer') {
        form.client_id = null
    }
}

function onPasswordModeChange(): void {
    if (form.password_mode === 'email') {
        form.is_active = false
    }
}

function focusableElements(): HTMLElement[] {
    return dialog.value
        ? Array.from(dialog.value.querySelectorAll<HTMLElement>('button:not([disabled]), input:not([disabled]), select:not([disabled])'))
        : []
}

function handleKeydown(event: KeyboardEvent): void {
    if (!isModalOpen.value) {
        return
    }

    if (event.key === 'Escape') {
        event.preventDefault()
        void closeModal()
        return
    }

    if (event.key !== 'Tab') {
        return
    }

    const elements = focusableElements()
    const first = elements[0]
    const last = elements[elements.length - 1]

    if (!first || !last) {
        event.preventDefault()
    } else if (event.shiftKey && document.activeElement === first) {
        event.preventDefault()
        last.focus()
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault()
        first.focus()
    }
}

watch(isModalOpen, async (open) => {
    if (open) {
        await nextTick()
        firstField.value?.focus()
    }
})

onBeforeUnmount(() => document.removeEventListener('keydown', handleKeydown))
document.addEventListener('keydown', handleKeydown)
</script>

<template>
    <section aria-labelledby="users-title" class="mx-auto w-full max-w-7xl">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 id="users-title" class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">{{ copy.title }}</h1>
                <p class="mt-1.5 max-w-2xl text-sm leading-6 text-slate-600">{{ copy.description }}</p>
            </div>
            <Button ref="createTrigger" type="button" class="shrink-0" @click="openModal">
                <Plus :size="16" aria-hidden="true" />
                {{ copy.create }}
            </Button>
        </div>

        <Card class="mt-6 overflow-hidden">
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-base"><UsersRound :size="18" aria-hidden="true" />{{ copy.title }}</CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                <div class="overflow-x-auto" tabindex="0">
                    <table class="w-full min-w-[760px] text-start text-sm">
                        <thead class="border-y border-border bg-muted/40 text-xs text-muted-foreground">
                            <tr>
                                <th v-for="key in ['name', 'email', 'mobile', 'role', 'client', 'status']" :key="key" scope="col" class="px-4 py-3 text-start font-medium">{{ copy.table[key] }}</th>
                            </tr>
                        </thead>
                        <tbody v-if="users.data.length" class="divide-y divide-border">
                            <tr v-for="user in users.data" :key="user.id" class="text-slate-700">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ user.name }} {{ user.last_name }}</td>
                                <td class="px-4 py-3">{{ user.email }}</td>
                                <td class="px-4 py-3">{{ user.mobile || copy.not_available }}</td>
                                <td class="px-4 py-3">{{ copy.roles[user.role] || user.role }}</td>
                                <td class="px-4 py-3">{{ user.client?.name || copy.not_available }}</td>
                                <td class="px-4 py-3"><span :class="user.is_active ? 'text-emerald-700' : 'text-slate-500'">{{ user.is_active ? copy.active : copy.inactive }}</span></td>
                            </tr>
                        </tbody>
                        <tbody v-else>
                            <tr><td colspan="6" class="px-4 py-12 text-center text-sm text-muted-foreground">{{ copy.empty }}</td></tr>
                        </tbody>
                    </table>
                </div>
                <nav v-if="users.links.length > 3" :aria-label="copy.pagination" class="flex flex-wrap gap-1 border-t border-border p-4">
                    <a v-for="link in users.links" :key="link.label + link.url" :href="link.url || undefined" class="rounded-md px-3 py-1.5 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" :class="link.active ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted'" :aria-current="link.active ? 'page' : undefined">{{ link.label }}</a>
                </nav>
            </CardContent>
        </Card>

        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4" @click.self="void closeModal()">
            <section ref="dialog" role="dialog" aria-modal="true" aria-labelledby="create-user-title" :aria-describedby="form.hasErrors ? 'create-user-errors' : undefined" class="max-h-[calc(100vh-2rem)] w-full max-w-2xl overflow-y-auto rounded-lg bg-white shadow-xl">
                <div class="flex items-start justify-between gap-4 border-b border-border p-5">
                    <div><h2 id="create-user-title" class="text-lg font-semibold text-slate-900">{{ copy.form.title }}</h2><p class="mt-1 text-sm text-muted-foreground">{{ copy.form.description }}</p></div>
                    <Button type="button" variant="ghost" size="icon" :aria-label="copy.close" :disabled="form.processing" @click="void closeModal"><X :size="18" aria-hidden="true" /></Button>
                </div>
                <form class="grid gap-5 p-5" @submit.prevent="submit">
                    <div v-if="form.hasErrors" id="create-user-errors" class="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-800" role="alert">{{ Object.values(form.errors)[0] }}</div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="grid gap-1.5 text-sm font-medium text-slate-700">{{ copy.form.name }}<Input ref="firstField" v-model="form.name" name="name" autocomplete="given-name" :aria-invalid="!!fieldError('name')" :aria-describedby="fieldError('name') ? 'name-error' : undefined" /> <span v-if="fieldError('name')" id="name-error" role="alert" class="text-xs font-normal text-red-700">{{ fieldError('name') }}</span></label>
                        <label class="grid gap-1.5 text-sm font-medium text-slate-700">{{ copy.form.last_name }}<Input v-model="form.last_name" name="last_name" autocomplete="family-name" :aria-invalid="!!fieldError('last_name')" /> <span v-if="fieldError('last_name')" role="alert" class="text-xs font-normal text-red-700">{{ fieldError('last_name') }}</span></label>
                    </div>
                    <label class="grid gap-1.5 text-sm font-medium text-slate-700">{{ copy.form.email }}<Input v-model="form.email" type="email" name="email" autocomplete="email" :aria-invalid="!!fieldError('email')" /> <span v-if="fieldError('email')" role="alert" class="text-xs font-normal text-red-700">{{ fieldError('email') }}</span></label>
                    <label class="grid gap-1.5 text-sm font-medium text-slate-700">{{ copy.form.mobile }}<Input v-model="form.mobile" name="mobile" autocomplete="tel" :aria-invalid="!!fieldError('mobile')" /> <span v-if="fieldError('mobile')" role="alert" class="text-xs font-normal text-red-700">{{ fieldError('mobile') }}</span></label>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="grid gap-1.5 text-sm font-medium text-slate-700">{{ copy.form.role }}<select v-model="form.role" name="role" class="h-9 rounded-md border border-input bg-background px-3 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" :aria-invalid="!!fieldError('role')" @change="onRoleChange"><option value="" disabled>{{ copy.form.role_placeholder }}</option><option v-for="role in roles" :key="role" :value="role">{{ copy.form[role] || role }}</option></select><span v-if="fieldError('role')" role="alert" class="text-xs font-normal text-red-700">{{ fieldError('role') }}</span></label>
                        <label v-if="form.role === 'customer'" class="grid gap-1.5 text-sm font-medium text-slate-700">{{ copy.form.client }}<select v-model="form.client_id" name="client_id" class="h-9 rounded-md border border-input bg-background px-3 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" :aria-invalid="!!fieldError('client_id')"><option :value="null" disabled>{{ copy.form.client_placeholder }}</option><option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }}</option></select><span v-if="fieldError('client_id')" role="alert" class="text-xs font-normal text-red-700">{{ fieldError('client_id') }}</span></label>
                    </div>
                    <label class="flex items-center gap-2 text-sm font-medium text-slate-700"><input v-model="form.is_active" type="checkbox" name="is_active" :disabled="form.password_mode === 'email'" class="size-4 rounded border-input text-primary focus:ring-primary" />{{ copy.form.active }}</label>
                    <fieldset class="grid gap-3"><legend class="text-sm font-medium text-slate-700">{{ copy.form.password_mode }}</legend><label class="flex items-start gap-2 text-sm"><input v-model="form.password_mode" type="radio" value="manual" name="password_mode" @change="onPasswordModeChange" /><span>{{ copy.form.manual_password }}<small class="block text-muted-foreground">{{ copy.form.manual_hint }}</small></span></label><label class="flex items-start gap-2 text-sm"><input v-model="form.password_mode" type="radio" value="email" name="password_mode" @change="onPasswordModeChange" /><span>{{ copy.form.email_password }}<small class="block text-muted-foreground">{{ copy.form.email_hint }}</small></span></label></fieldset>
                    <div v-if="form.password_mode === 'manual'" class="grid gap-4 sm:grid-cols-2"><label class="grid gap-1.5 text-sm font-medium text-slate-700">{{ copy.form.password }}<Input v-model="form.password" type="password" name="password" autocomplete="new-password" :aria-invalid="!!fieldError('password')" /> <span v-if="fieldError('password')" role="alert" class="text-xs font-normal text-red-700">{{ fieldError('password') }}</span></label><label class="grid gap-1.5 text-sm font-medium text-slate-700">{{ copy.form.password_confirmation }}<Input v-model="form.password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" /></label></div>
                    <div class="flex justify-end gap-2 border-t border-border pt-4"><Button type="button" variant="outline" :disabled="form.processing" @click="void closeModal">{{ copy.close }}</Button><Button type="submit" :disabled="form.processing"><span v-if="form.processing">{{ copy.form.submitting }}</span><span v-else>{{ copy.form.submit }}</span></Button></div>
                </form>
            </section>
        </div>
    </section>
</template>
