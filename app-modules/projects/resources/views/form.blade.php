<div>
    <x-ui.page-header :title="$projectId ? __('projects::messages.edit_project') : __('projects::messages.new_project')" />

    <form class="max-w-4xl" wire:submit="save">
        <x-ui.card>
            <div class="space-y-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="name" :label="__('app.title')" :value="$name" wire:model="name" required />

                    @if($projectId)
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">مشتری</label>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-semibold text-slate-800">{{ $clientName }}</div>
                            <p class="mt-1 text-xs text-slate-500">مشتری پروژه بعد از ایجاد قابل تغییر نیست.</p>
                        </div>
                    @else
                        <x-ui.select name="client_id" label="مشتری" wire:model.live.number="client_id" required>
                            <option value="">—</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </x-ui.select>
                    @endif
                </div>

                <x-ui.textarea name="description" :label="__('app.description')" :value="$description" wire:model="description" />

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="start_date" type="date" label="تاریخ شروع" :value="$start_date" wire:model="start_date" />
                    <x-ui.input name="due_date" type="date" label="موعد" :value="$due_date" wire:model="due_date" />
                </div>

                <div>
                    <div class="mb-2 text-sm font-semibold text-slate-700">اعضای مشتری پروژه</div>
                    @if(!$client_id)
                        <p class="text-sm text-slate-500">ابتدا مشتری را انتخاب کنید.</p>
                    @else
                        <div class="grid gap-2 sm:grid-cols-2">
                            @forelse($members as $member)
                                <x-ui.checkbox name="member_ids[]" :label="$member->full_name" :value="$member->id" model="member_ids" />
                            @empty
                                <p class="text-sm text-slate-500">کاربر فعال قابل عضویت وجود ندارد.</p>
                            @endforelse
                        </div>
                    @endif
                    @error('member_ids')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    @error('member_ids.*')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <x-ui.form-actions>
                    <x-ui.button type="submit" icon="fa-floppy-disk" wire:loading.attr="disabled" wire:target="save">{{ __('app.save') }}</x-ui.button>
                    <x-ui.button variant="secondary" :href="route('projects.index')" icon="fa-xmark" wire:navigate>{{ __('app.cancel') }}</x-ui.button>
                </x-ui.form-actions>
            </div>
        </x-ui.card>
    </form>
</div>
