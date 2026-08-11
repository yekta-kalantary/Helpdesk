<div>
    @if(session('success'))
        <x-ui.alert class="mb-5" tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header title="پروفایل" />

    <form class="max-w-3xl" wire:submit="save">
        <x-ui.card>
            <div class="space-y-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="name" :label="__('app.name_label')" :value="$name" wire:model="name" required />
                    <x-ui.input name="last_name" :label="__('app.last_name')" :value="$last_name" wire:model="last_name" required />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="password" label="رمز عبور جدید" type="password" wire:model="password" autocomplete="new-password" />
                    <x-ui.input name="password_confirmation" label="تکرار رمز عبور" type="password" wire:model="password_confirmation" autocomplete="new-password" />
                </div>

                <p class="text-sm text-slate-500">ایمیل، نقش، مشتری و وضعیت حساب فقط توسط ادمین مدیریت می‌شوند.</p>

                <x-ui.form-actions>
                    <x-ui.button type="submit" icon="fa-floppy-disk" wire:loading.attr="disabled" wire:target="save">{{ __('app.save') }}</x-ui.button>
                </x-ui.form-actions>
            </div>
        </x-ui.card>
    </form>
</div>
