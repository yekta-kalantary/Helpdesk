<div>
    <x-ui.page-header :title="$projectId ? __('projects::messages.edit_project') : __('projects::messages.new_project')" />

    <form class="max-w-5xl" wire:submit="save">
        <x-ui.card>
            <div class="space-y-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="title" :label="__('app.title')" :value="$title" wire:model="title" required />

                    <x-ui.select name="category" :label="__('projects::messages.category')" wire:model.live="category" required>
                        @foreach($categories as $categoryItem)
                            <option value="{{ $categoryItem->value }}">{{ __('projects::messages.category.'.$categoryItem->value) }}</option>
                        @endforeach
                    </x-ui.select>

                    @if($category === 'contact')
                        <x-ui.searchable-select
                            name="contact_id"
                            :label="__('projects::messages.contact')"
                            :options="$options['contacts']"
                            :value="$contact_id"
                            search-model="contactSearch"
                            select-action="selectContact"
                            :search-placeholder="__('projects::messages.contact_search_placeholder')"
                            required
                        />
                    @else
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                            {{ __('projects::messages.internal_project_hint') }}
                        </div>
                    @endif

                    <x-ui.select name="type" :label="__('projects::messages.type')" wire:model="type" required>
                        @foreach($types as $typeItem)
                            <option value="{{ $typeItem->value }}">{{ __('projects::messages.type.'.$typeItem->value) }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="status" :label="__('app.status')" wire:model="status" required>
                        @foreach($statuses as $statusItem)
                            <option value="{{ $statusItem->value }}">{{ __('projects::messages.status.'.$statusItem->value) }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input name="starts_at" :label="__('projects::messages.starts_at')" type="date" dir="ltr" :value="$starts_at" wire:model="starts_at" />
                    <x-ui.input name="ends_at" :label="__('projects::messages.ends_at')" type="date" dir="ltr" :value="$ends_at" wire:model="ends_at" />
                </div>

                <x-ui.textarea name="description" :label="__('app.description')" :value="$description" wire:model="description" />

                <div>
                    <div class="mb-2 text-sm font-semibold text-slate-700">{{ __('projects::messages.members') }}</div>
                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($options['members'] as $member)
                            <x-ui.checkbox name="member_ids[]" :label="$member['name']" :value="$member['id']" model="member_ids" />
                        @endforeach
                    </div>
                    @error('member_ids.*')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <x-ui.form-actions>
                    <x-ui.button type="submit" icon="fa-floppy-disk" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">{{ __('app.save') }}</span>
                        <span wire:loading wire:target="save">{{ __('app.loading') }}</span>
                    </x-ui.button>
                    <x-ui.button variant="secondary" :href="route('projects.index')" icon="fa-xmark" wire:navigate>{{ __('app.cancel') }}</x-ui.button>
                </x-ui.form-actions>
            </div>
        </x-ui.card>
    </form>
</div>
