<div>
    <x-ui.page-header :title="$clientId ? 'ویرایش مشتری' : 'مشتری جدید'" />

    <form class="max-w-3xl" wire:submit="save">
        <div class="space-y-4">
            <x-ui.card title="۱. هویت مشتری" subtitle="نام و وضعیت مشتری را مشخص کنید.">
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.input name="name" label="نام مشتری" :value="$name" wire:model="name" required />
                    <x-ui.select name="status" label="وضعیت" wire:model="status" required>
                        <option value="active">فعال</option>
                        <option value="inactive">غیرفعال</option>
                    </x-ui.select>
                </div>
            </x-ui.card>

            <x-ui.card title="۲. زمینه" subtitle="توضیح کوتاهی برای نگهداری زمینه مشتری اضافه کنید.">
                <x-ui.textarea name="description" :label="__('app.description').' (اختیاری)'" :value="$description" wire:model="description" hint="اطلاعاتی که برای تیم در مراجعات بعدی مفید است." />
            </x-ui.card>

            <x-ui.form-actions mobile-sticky>
                <x-ui.button type="submit" icon="fa-floppy-disk" wire:loading.attr="disabled" wire:target="save">{{ __('app.save') }}</x-ui.button>
                <x-ui.button variant="secondary" :href="route('clients.index')" icon="fa-xmark" wire:navigate>{{ __('app.cancel') }}</x-ui.button>
            </x-ui.form-actions>
        </div>
    </form>
</div>
