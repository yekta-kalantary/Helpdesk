<div>
    <x-ui.page-header :title="$clientId ? 'ویرایش مشتری' : 'مشتری جدید'" />

    <form class="max-w-3xl" wire:submit="save">
        <x-ui.card>
            <div class="space-y-5">
                <x-ui.input name="name" label="نام مشتری" :value="$name" wire:model="name" required />
                <x-ui.textarea name="description" :label="__('app.description')" :value="$description" wire:model="description" />
                <x-ui.select name="status" label="وضعیت" wire:model="status" required>
                    <option value="active">فعال</option>
                    <option value="inactive">غیرفعال</option>
                </x-ui.select>

                <x-ui.form-actions>
                    <x-ui.button type="submit" icon="fa-floppy-disk" wire:loading.attr="disabled" wire:target="save">{{ __('app.save') }}</x-ui.button>
                    <x-ui.button variant="secondary" :href="route('clients.index')" icon="fa-xmark" wire:navigate>{{ __('app.cancel') }}</x-ui.button>
                </x-ui.form-actions>
            </div>
        </x-ui.card>
    </form>
</div>
