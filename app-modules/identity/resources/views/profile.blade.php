<div>
    @if(session('success'))
        <x-ui.alert class="mb-5" tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header title="پروفایل" subtitle="اطلاعات قابل ویرایش حساب و رمز عبور خود را مدیریت کنید." />

    <form class="max-w-3xl" wire:submit="save">
        <div class="divide-y divide-border rounded-surface border border-border bg-surface px-4 sm:px-6">
            <section class="py-5 sm:py-6" aria-labelledby="profile-identity-heading">
                <div class="mb-5">
                    <h2 id="profile-identity-heading" class="font-bold text-text">اطلاعات پایه</h2>
                    <p class="mt-1 text-body-sm leading-6 text-text-muted">نامی که در پنل و فعالیت‌های شما نمایش داده می‌شود.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="name" :label="__('app.name_label')" :value="$name" wire:model="name" required />
                    <x-ui.input name="last_name" :label="__('app.last_name')" :value="$last_name" wire:model="last_name" required />
                </div>
            </section>

            <section class="py-5 sm:py-6" aria-labelledby="profile-security-heading">
                <div class="mb-5">
                    <h2 id="profile-security-heading" class="font-bold text-text">امنیت حساب</h2>
                    <p class="mt-1 text-body-sm leading-6 text-text-muted">برای نگه‌داشتن رمز فعلی، این بخش را خالی بگذارید.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="password" label="رمز عبور جدید" type="password" wire:model="password" autocomplete="new-password" />
                    <x-ui.input name="password_confirmation" label="تکرار رمز عبور" type="password" wire:model="password_confirmation" autocomplete="new-password" />
                </div>
            </section>

            <section class="py-5 sm:py-6" aria-labelledby="profile-access-heading">
                <div class="mb-3">
                    <h2 id="profile-access-heading" class="font-bold text-text">سطح دسترسی</h2>
                    <p class="mt-1 text-body-sm leading-6 text-text-muted">این موارد برای حفظ دسترسی درست به فضاهای کاری قابل ویرایش نیستند.</p>
                </div>
                <p class="text-body-sm leading-6 text-text-muted">ایمیل، نقش، مشتری و وضعیت حساب فقط توسط ادمین مدیریت می‌شوند.</p>
            </section>

            <x-ui.form-actions class="sticky bottom-0 z-10 -mx-4 border-t border-border bg-page/95 px-4 py-3 sm:static sm:mx-0 sm:border-0 sm:bg-transparent sm:px-0 sm:py-5">
                <x-ui.button type="submit" icon="fa-floppy-disk" wire:loading.attr="disabled" wire:target="save">{{ __('app.save') }}</x-ui.button>
            </x-ui.form-actions>
        </div>
    </form>
</div>
