<div>
    @if(session('success'))
        <x-ui.alert class="mb-5" tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header title="پروفایل" subtitle="اطلاعات قابل ویرایش حساب و رمز عبور خود را مدیریت کنید." />

    <form class="max-w-3xl" wire:submit="save">
        <div class="space-y-4">
            <x-ui.card title="اطلاعات پایه" subtitle="نامی که در پنل و فعالیت‌های شما نمایش داده می‌شود.">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="name" :label="__('app.name_label')" :value="$name" wire:model="name" required />
                    <x-ui.input name="last_name" :label="__('app.last_name')" :value="$last_name" wire:model="last_name" required />
                </div>
            </x-ui.card>

            <x-ui.card title="امنیت حساب" subtitle="برای نگه‌داشتن رمز فعلی، این بخش را خالی بگذارید.">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="password" label="رمز عبور جدید" type="password" wire:model="password" autocomplete="new-password" />
                    <x-ui.input name="password_confirmation" label="تکرار رمز عبور" type="password" wire:model="password_confirmation" autocomplete="new-password" />
                </div>
            </x-ui.card>

            <x-ui.card title="سطح دسترسی" subtitle="این موارد برای حفظ دسترسی درست به فضاهای کاری قابل ویرایش نیستند.">
                <p class="text-sm leading-6 text-slate-500">ایمیل، نقش، مشتری و وضعیت حساب فقط توسط ادمین مدیریت می‌شوند.</p>
            </x-ui.card>

            <x-ui.form-actions class="sticky bottom-0 z-10 -mx-4 bg-workspace-page/95 px-4 pb-1 backdrop-blur sm:static sm:mx-0 sm:bg-transparent sm:px-0 sm:pb-0 sm:backdrop-blur-none">
                    <x-ui.button type="submit" icon="fa-floppy-disk" wire:loading.attr="disabled" wire:target="save">{{ __('app.save') }}</x-ui.button>
            </x-ui.form-actions>
        </div>
    </form>
</div>
