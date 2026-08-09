<div>
    <x-ui.page-header :title="__('tickets::messages.new_ticket')" />

    <form class="max-w-5xl" wire:submit="save">
        <x-ui.card>
            <div class="space-y-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    @if(! $scope['customer_id'])
                        <x-ui.select name="customer_id" :label="__('tickets::messages.customer')" wire:model.number="customer_id" required>
                            <option value="">—</option>
                            @foreach($options['customers'] as $customer)
                                <option value="{{ $customer['id'] }}">{{ $customer['name'] }}</option>
                            @endforeach
                        </x-ui.select>
                    @endif

                    <x-ui.select name="project_id" :label="__('tickets::messages.project')" wire:model.number="project_id">
                        <option value="">{{ __('tickets::messages.no_project') }}</option>
                        @foreach($options['projects'] as $project)
                            <option value="{{ $project['id'] }}">{{ $project['name'] }}</option>
                        @endforeach
                    </x-ui.select>

                    <div class="sm:col-span-2">
                        <x-ui.input name="subject" :label="__('tickets::messages.subject')" :value="$subject" wire:model="subject" required />
                    </div>

                    <x-ui.select name="category" :label="__('tickets::messages.category')" wire:model="category">
                        @foreach($categories as $categoryItem)
                            <option value="{{ $categoryItem->value }}">{{ __('tickets::messages.category.'.$categoryItem->value) }}</option>
                        @endforeach
                    </x-ui.select>

                    <x-ui.select name="priority" :label="__('tickets::messages.priority')" wire:model="priority">
                        @foreach($priorities as $priorityItem)
                            <option value="{{ $priorityItem->value }}">{{ __('tickets::messages.priority.'.$priorityItem->value) }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <x-ui.textarea name="body" :label="__('tickets::messages.message')" :value="$body" wire:model="body" required />
                <x-ui.input name="attachments" :label="__('tickets::messages.attachments')" type="file" wire:model="attachments" multiple />
                @error('attachments.*')<p class="text-xs font-medium text-red-600">{{ $message }}</p>@enderror

                <x-ui.form-actions>
                    <x-ui.button type="submit" icon="fa-plus" wire:loading.attr="disabled" wire:target="save,attachments">
                        <span wire:loading.remove wire:target="save">{{ __('app.create') }}</span>
                        <span wire:loading wire:target="save">{{ __('app.loading') }}</span>
                    </x-ui.button>
                    <x-ui.button variant="secondary" :href="route('tickets.index')" icon="fa-xmark" wire:navigate>{{ __('app.cancel') }}</x-ui.button>
                </x-ui.form-actions>
            </div>
        </x-ui.card>
    </form>
</div>
