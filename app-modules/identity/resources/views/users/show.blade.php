<div class="space-y-6">
    @if(session('success'))
        <x-ui.alert tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header :title="$user->full_name" subtitle="اطلاعات هویتی، وضعیت دسترسی و عضویت‌های فعال این کاربر.">
        <x-slot:actions>
            <x-ui.button variant="secondary" :href="route('users.index')" icon="fa-arrow-right" wire:navigate>{{ __('identity::messages.back_to_users') }}</x-ui.button>
            <x-ui.button variant="secondary" wire:click="sendSetupLink" icon="fa-envelope">ارسال لینک تنظیم رمز</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-8 xl:grid-cols-[2fr_1fr]">
        <form wire:submit="saveProfile">
            <div class="divide-y divide-workspace-divider rounded-workspace border border-workspace-divider bg-workspace-surface px-4 sm:px-6">
                <section class="py-5 sm:py-6" aria-labelledby="user-contact-heading">
                    <div class="mb-5"><h2 id="user-contact-heading" class="font-bold text-workspace-text">اطلاعات تماس</h2><p class="mt-1 text-sm leading-6 text-workspace-muted">ایمیل و موبایل برای ارتباط با این کاربر استفاده می‌شوند.</p></div>
                    <div class="space-y-5">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.input name="name" :label="__('app.name_label')" :value="$name" wire:model="name" required />
                            <x-ui.input name="last_name" :label="__('app.last_name')" :value="$last_name" wire:model="last_name" required />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.input name="email" type="email" label="ایمیل" dir="ltr" :value="$email" wire:model="email" required />
                            <x-ui.input name="mobile" label="موبایل" dir="ltr" :value="$mobile" wire:model="mobile" />
                        </div>
                    </div>
                </section>

                <section class="py-5 sm:py-6" aria-labelledby="user-security-heading">
                    <h2 id="user-security-heading" class="mb-5 font-bold text-workspace-text">دسترسی و امنیت</h2>
                    <div class="space-y-5">
                        <x-ui.checkbox name="is_active" label="کاربر فعال باشد" model="is_active" />
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.input name="password" type="password" label="رمز عبور جدید" wire:model="password" autocomplete="new-password" />
                            <x-ui.input name="password_confirmation" type="password" label="تکرار رمز عبور" wire:model="password_confirmation" autocomplete="new-password" />
                        </div>
                    </div>
                </section>

                <x-ui.form-actions class="sticky bottom-0 z-10 -mx-4 bg-workspace-page/95 px-4 py-3 backdrop-blur sm:static sm:mx-0 sm:bg-transparent sm:px-0 sm:py-5 sm:backdrop-blur-none">
                    <x-ui.button type="submit" icon="fa-floppy-disk" wire:loading.attr="disabled" wire:target="saveProfile">{{ __('app.save') }}</x-ui.button>
                </x-ui.form-actions>
            </div>
        </form>

        <div class="space-y-5">
            <section aria-labelledby="user-summary-heading">
                <h2 id="user-summary-heading" class="mb-3 font-bold text-workspace-text">خلاصه حساب</h2>
                <dl class="space-y-4 text-sm">
                    <div><dt class="text-workspace-muted">مشتری</dt><dd class="mt-1 font-bold">{{ $user->client?->name ?? '—' }}</dd></div>
                    <div><dt class="text-workspace-muted">نقش</dt><dd class="mt-1 font-bold">{{ __('tasks::messages.roles.'.$user->role->value) }}</dd></div>
                    <div><dt class="text-workspace-muted">وضعیت</dt><dd class="mt-1"><x-ui.badge :tone="$user->is_active ? 'success' : 'neutral'">{{ $user->is_active ? 'فعال' : 'غیرفعال' }}</x-ui.badge></dd></div>
                    <div><dt class="text-workspace-muted">آخرین ورود</dt><dd class="mt-1 font-bold"><x-ui.date :value="$user->last_login_at" datetime />{{ $user->last_login_at ? '' : '—' }}</dd></div>
                </dl>
            </section>

            <section class="border-t border-workspace-divider pt-5" aria-labelledby="user-projects-heading">
                <h2 id="user-projects-heading" class="font-bold text-workspace-text">پروژه‌های فعال عضو</h2>
                <p class="mt-1 text-sm leading-6 text-workspace-muted">عضویت‌هایی که هنوز فعال هستند.</p>
                <div class="mt-4 divide-y divide-workspace-divider border-y border-workspace-divider">
                    @forelse($projects as $project)
                        <a href="{{ route('projects.show', $project) }}" wire:navigate class="block min-h-11 py-3 font-semibold text-workspace-text hover:text-workspace-accent">{{ $project->name }}</a>
                    @empty
                        <p class="text-sm text-workspace-muted">عضویت فعالی ندارد.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>
