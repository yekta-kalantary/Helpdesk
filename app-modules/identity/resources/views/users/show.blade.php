<div class="space-y-6">
    @if(session('success'))
        <x-ui.alert tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header :title="$user->full_name">
        <x-slot:actions>
            <x-ui.button variant="secondary" :href="route('users.index')" icon="fa-arrow-right" wire:navigate>{{ __('identity::messages.back_to_users') }}</x-ui.button>
            <x-ui.button variant="secondary" wire:click="sendSetupLink" icon="fa-envelope">ارسال لینک تنظیم رمز</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-5 xl:grid-cols-[2fr_1fr]">
        <form wire:submit="saveProfile">
            <x-ui.card>
                <div class="space-y-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.input name="name" :label="__('app.name_label')" :value="$name" wire:model="name" required />
                        <x-ui.input name="last_name" :label="__('app.last_name')" :value="$last_name" wire:model="last_name" required />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.input name="email" type="email" label="ایمیل" dir="ltr" :value="$email" wire:model="email" required />
                        <x-ui.input name="mobile" label="موبایل" dir="ltr" :value="$mobile" wire:model="mobile" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.input name="password" type="password" label="رمز عبور جدید" wire:model="password" />
                        <x-ui.input name="password_confirmation" type="password" label="تکرار رمز عبور" wire:model="password_confirmation" />
                    </div>

                    <x-ui.checkbox name="is_active" label="کاربر فعال باشد" model="is_active" />

                    <x-ui.form-actions>
                        <x-ui.button type="submit" icon="fa-floppy-disk" wire:loading.attr="disabled" wire:target="saveProfile">{{ __('app.save') }}</x-ui.button>
                    </x-ui.form-actions>
                </div>
            </x-ui.card>
        </form>

        <div class="space-y-5">
            <x-ui.card>
                <dl class="space-y-4 text-sm">
                    <div><dt class="text-slate-500">مشتری</dt><dd class="mt-1 font-bold">{{ $user->client?->name ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">نقش</dt><dd class="mt-1 font-bold">{{ __('tasks::messages.roles.'.$user->role->value) }}</dd></div>
                    <div><dt class="text-slate-500">وضعیت</dt><dd class="mt-1"><x-ui.badge :tone="$user->is_active ? 'success' : 'neutral'">{{ $user->is_active ? 'فعال' : 'غیرفعال' }}</x-ui.badge></dd></div>
                    <div><dt class="text-slate-500">آخرین ورود</dt><dd class="mt-1 font-bold"><x-ui.date :value="$user->last_login_at" datetime />{{ $user->last_login_at ? '' : '—' }}</dd></div>
                </dl>
            </x-ui.card>

            <x-ui.card>
                <h2 class="mb-3 font-black">پروژه‌های فعال عضو</h2>
                <div class="space-y-2">
                    @forelse($projects as $project)
                        <a href="{{ route('projects.show', $project) }}" wire:navigate class="block rounded-xl border border-slate-200 p-3 font-bold hover:bg-slate-50">{{ $project->name }}</a>
                    @empty
                        <p class="text-sm text-slate-500">عضویت فعالی ندارد.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </div>
</div>
