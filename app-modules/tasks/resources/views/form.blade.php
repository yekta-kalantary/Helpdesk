<div>
    <x-ui.page-header :title="$taskId ? __('tasks::messages.edit_task') : __('tasks::messages.new_task')" />

    <form class="max-w-4xl" wire:submit="save">
        <x-ui.card>
            <div class="space-y-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="title" :label="__('app.title')" :value="$title" wire:model="title" required />

                    @if($taskId)
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">پروژه</label>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-semibold">{{ $projectName }}</div>
                            <p class="mt-1 text-xs text-slate-500">پروژه تسک بعد از ایجاد قابل تغییر نیست.</p>
                        </div>
                    @else
                        <x-ui.select name="project_id" :label="__('tasks::messages.project')" wire:model.live.number="project_id" required>
                            <option value="">—</option>
                            @foreach($projects as $project)<option value="{{ $project->id }}">{{ $project->name }}</option>@endforeach
                        </x-ui.select>
                    @endif
                </div>

                <x-ui.textarea name="description" :label="__('app.description')" :value="$description" wire:model="description" />

                @if($isAdmin)
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <x-ui.select name="status" label="وضعیت" wire:model.live="status" required>
                            @foreach($statuses as $statusItem)<option value="{{ $statusItem->value }}">{{ __('tasks::messages.statuses.'.$statusItem->value) }}</option>@endforeach
                        </x-ui.select>
                        <x-ui.select name="priority" label="اولویت" wire:model="priority" required>
                            @foreach($priorities as $priorityItem)<option value="{{ $priorityItem->value }}">{{ __('tasks::messages.priorities.'.$priorityItem->value) }}</option>@endforeach
                        </x-ui.select>
                        <x-ui.select name="assigned_to" label="مسئول" wire:model="assigned_to">
                            <option value="">{{ __('tasks::messages.assignee.none') }} / {{ __('tasks::messages.assignee.admin_queue') }}</option>
                            @foreach($assignees as $assignee)<option value="{{ $assignee->id }}">{{ $assignee->full_name }}</option>@endforeach
                        </x-ui.select>
                        <x-ui.input name="due_date" type="date" label="موعد" :value="$due_date" wire:model="due_date" />
                    </div>
                @else
                    <x-ui.alert tone="neutral">درخواست مشتری با اولویت عادی و وضعیت «منتظر ادمین» وارد صف ادمین می‌شود.</x-ui.alert>
                @endif

                @if(!$taskId)
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">فایل پیوست اختیاری</label>
                        <input type="file" wire:model="attachment" class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm" />
                        @error('attachment')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                        <p class="mt-1 text-xs text-slate-500">حداکثر ۲۰ مگابایت؛ فایل فقط از مسیر احراز هویت‌شده قابل دریافت است.</p>
                    </div>
                @endif

                <x-ui.form-actions>
                    <x-ui.button type="submit" icon="fa-floppy-disk" wire:loading.attr="disabled" wire:target="save,attachment">
                        <span wire:loading.remove wire:target="save">{{ __('app.save') }}</span>
                        <span wire:loading wire:target="save">{{ __('app.loading') }}</span>
                    </x-ui.button>
                    <x-ui.button variant="secondary" :href="route('tasks.index')" icon="fa-xmark" wire:navigate>{{ __('app.cancel') }}</x-ui.button>
                </x-ui.form-actions>
            </div>
        </x-ui.card>
    </form>
</div>
