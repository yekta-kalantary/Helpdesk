<div>
    <x-ui.page-header :title="$taskId ? __('tasks::messages.edit_task') : __('tasks::messages.new_task')" />

    <form class="max-w-4xl" wire:submit="save" wire:loading.class="opacity-60" wire:target="save" data-task-form>
        <x-ui.card>
            <div class="space-y-7">
                <section aria-labelledby="task-context-heading" class="space-y-4">
                    <div class="border-b border-border pb-4">
                        <p class="text-label font-semibold uppercase tracking-[0.16em] text-primary">۱</p>
                        <h2 id="task-context-heading" class="mt-1 text-heading-lg font-semibold text-text">زمینه پروژه</h2>
                        <p class="mt-1 text-body-sm text-text-muted">پروژه، وضعیت و ساختار کاری تسک را مشخص کنید.</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="title" :label="__('app.title')" :value="$title" wire:model="title" required />

                    @if($taskId)
                        <div>
                            <label class="mb-2 block text-label font-semibold text-text">پروژه</label>
                            <div class="rounded-control border border-border bg-page px-4 py-3 font-semibold text-text">{{ $projectName }}</div>
                            <p class="mt-1 text-metadata text-text-muted">پروژه تسک بعد از ایجاد قابل تغییر نیست.</p>
                        </div>
                    @else
                        <x-ui.select name="project_id" :label="__('tasks::messages.project')" wire:model.live.number="project_id" required>
                            <option value="">—</option>
                            @foreach($projects as $project)<option value="{{ $project->id }}">{{ $project->name }}</option>@endforeach
                        </x-ui.select>
                    @endif
                    </div>

                </section>

                <section aria-labelledby="task-content-heading" class="space-y-4 border-t border-border pt-6">
                    <div>
                        <p class="text-label font-semibold uppercase tracking-[0.16em] text-primary">۲</p>
                        <h2 id="task-content-heading" class="mt-1 text-heading-lg font-semibold text-text">محتوا</h2>
                        <p class="mt-1 text-body-sm text-text-muted">عنوان و توضیحی بنویسید که انجام کار را برای تیم روشن کند.</p>
                    </div>
                    <x-ui.textarea name="description" :label="__('app.description')" hint="جزئیات، نتیجه مورد انتظار یا زمینه لازم را اضافه کنید." :value="$description" wire:model="description" />
                </section>

                @if($project_id)
                    <section aria-labelledby="task-ownership-heading" class="space-y-4 border-t border-border pt-6">
                        <div>
                            <p class="text-label font-semibold uppercase tracking-[0.16em] text-primary">۳</p>
                            <h2 id="task-ownership-heading" class="mt-1 text-heading-lg font-semibold text-text">مالکیت</h2>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.select name="project_status_id" label="وضعیت پروژه‌ای تسک" wire:model="project_status_id">
                            <option value="">اولین وضعیت باز پروژه (پیش‌فرض)</option>
                            @foreach($statuses as $statusItem)
                                <option value="{{ $statusItem->id }}">{{ $statusItem->title }}{{ $statusItem->is_done ? ' · Done' : '' }}</option>
                            @endforeach
                        </x-ui.select>

                        @if($isAdmin)
                            <x-ui.select name="work_group_id" label="Work Group" wire:model="work_group_id">
                                <option value="">ریشه پروژه</option>
                                @foreach($workGroups as $group)
                                    <option value="{{ $group->id }}">{{ $group->title }}</option>
                                @endforeach
                            </x-ui.select>
                        @endif
                        </div>
                    </section>
                @endif

                @if($isAdmin)
                    <section aria-labelledby="task-scheduling-heading" class="space-y-4 border-t border-border pt-6">
                        <div>
                            <p class="text-label font-semibold uppercase tracking-[0.16em] text-primary">۴</p>
                            <h2 id="task-scheduling-heading" class="mt-1 text-heading-lg font-semibold text-text">زمان‌بندی و تخصیص</h2>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        <x-ui.select name="priority" label="اولویت" wire:model="priority" required>
                            @foreach($priorities as $priorityItem)<option value="{{ $priorityItem->value }}">{{ __('tasks::messages.priorities.'.$priorityItem->value) }}</option>@endforeach
                        </x-ui.select>
                        <x-ui.select name="assigned_to" label="مسئول" wire:model="assigned_to">
                            <option value="">{{ __('tasks::messages.assignee.none') }}</option>
                            @foreach($assignees as $assignee)<option value="{{ $assignee->id }}">{{ $assignee->full_name }}</option>@endforeach
                        </x-ui.select>
                        <x-ui.input name="due_date" type="date" label="موعد" hint="در صورت نیاز، آخرین مهلت انجام کار را تعیین کنید." :value="$due_date" wire:model="due_date" />
                        </div>
                    </section>
                @else
                    <x-ui.alert tone="neutral">می‌توانید وضعیت اولیه را از Workflow همین پروژه انتخاب کنید. تسک مشتری در ریشه پروژه ایجاد می‌شود و انتقال آن به Work Group، تعیین مسئول و موعد در اختیار ادمین است.</x-ui.alert>
                @endif

                @if(!$taskId)
                    <div>
                        <label class="mb-2 block text-label font-semibold text-text">فایل پیوست اختیاری</label>
                        <input type="file" wire:model="attachment" class="block min-h-11 w-full rounded-control border border-border bg-surface px-3 py-2 text-body-sm" />
                        @error('attachment')<p class="mt-2 text-metadata font-medium text-danger-text">{{ $message }}</p>@enderror
                        <p class="mt-1 text-metadata text-text-muted">حداکثر ۲۰ مگابایت؛ فایل فقط از مسیر احراز هویت‌شده قابل دریافت است.</p>
                    </div>
                @endif

                @error('project_status_id')<x-ui.alert tone="danger">{{ $message }}</x-ui.alert>@enderror

                <x-ui.form-actions mobile-sticky>
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
