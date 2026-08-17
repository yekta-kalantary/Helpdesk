<div>
    <x-ui.page-header :title="$projectId ? __('projects::messages.edit_project') : __('projects::messages.new_project')" />

    <form class="max-w-4xl" wire:submit="save">
        <div class="space-y-4">
            <x-ui.card title="۱. هویت پروژه" subtitle="نام پروژه را مشخص کنید.">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="name" :label="__('app.title')" :value="$name" hint="الزامی" wire:model="name" required />
                </div>
            </x-ui.card>

            <x-ui.card title="۲. زمینه پروژه" subtitle="مشتری و توضیحات این پروژه را مشخص کنید.">
                <div class="space-y-5">
                    @if($projectId)
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-workspace-text">مشتری <span class="text-xs font-normal text-workspace-muted">الزامی</span></label>
                            <div class="rounded-workspace border border-workspace-border bg-workspace-neutral-surface px-4 py-3 font-semibold text-workspace-text">{{ $clientName }}</div>
                            <p class="mt-1 text-xs text-workspace-muted">مشتری پروژه بعد از ایجاد قابل تغییر نیست.</p>
                        </div>
                    @else
                        <x-ui.select name="client_id" label="مشتری (الزامی)" wire:model.live.number="client_id" required>
                            <option value="">—</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </x-ui.select>
                    @endif
                    <x-ui.textarea name="description" :label="__('app.description').' (اختیاری)'" :value="$description" wire:model="description" />
                </div>
            </x-ui.card>

            <x-ui.card title="۳. عضویت" subtitle="اعضای فعال این مشتری را به پروژه اضافه کنید.">
                <div>
                    <div class="mb-2 text-sm font-semibold text-workspace-text">اعضای مشتری پروژه</div>
                    @if(!$client_id)
                        <p class="text-sm text-workspace-muted">ابتدا مشتری را انتخاب کنید.</p>
                    @else
                        <div class="grid gap-2 sm:grid-cols-2">
                            @forelse($members as $member)
                                <x-ui.checkbox
                                    name="member_ids[]"
                                    :label="$member->full_name"
                                    :hint="!$member->is_active ? 'کاربر غیرفعال؛ برای حذف عضویت، انتخاب را بردارید.' : null"
                                    :value="$member->id"
                                    model="member_ids"
                                />
                            @empty
                                <p class="text-sm text-workspace-muted">کاربر فعال قابل عضویت وجود ندارد.</p>
                            @endforelse
                        </div>
                    @endif
                    @error('member_ids')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    @error('member_ids.*')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
            </x-ui.card>

            <x-ui.card title="۴. زمان‌بندی" subtitle="تاریخ‌های شروع و موعد اختیاری هستند.">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="start_date" type="date" label="تاریخ شروع (اختیاری)" :value="$start_date" wire:model="start_date" />
                    <x-ui.input name="due_date" type="date" label="موعد (اختیاری)" :value="$due_date" wire:model="due_date" />
                </div>
            </x-ui.card>

            <x-ui.form-actions>
                <x-ui.button type="submit" icon="fa-floppy-disk" wire:loading.attr="disabled" wire:target="save">{{ __('app.save') }}</x-ui.button>
                <x-ui.button variant="secondary" :href="route('projects.index')" icon="fa-xmark" wire:navigate>{{ __('app.cancel') }}</x-ui.button>
            </x-ui.form-actions>
        </div>
    </form>
</div>
