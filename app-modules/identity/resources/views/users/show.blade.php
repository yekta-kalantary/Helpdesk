<div>
    @if(session('success'))
        <x-ui.alert class="mb-5" tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header :title="$user->full_name">
        <x-slot:actions>
            <x-ui.button variant="secondary" :href="route('users.index')" icon="fa-arrow-right" wire:navigate>{{ __('identity::messages.back_to_users') }}</x-ui.button>
            <x-ui.button icon="fa-pen-to-square" wire:click="selectTab('general')">{{ __('identity::messages.edit_information') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="space-y-5">
        <x-ui.card>
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-lg font-black text-slate-700">
                        {{ mb_substr($user->name, 0, 1) }}{{ mb_substr($user->last_name, 0, 1) }}
                    </div>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="truncate text-lg font-black text-slate-950">{{ $user->full_name }}</h2>
                            <x-ui.badge :tone="$user->is_active ? 'success' : 'neutral'">
                                {{ $user->is_active ? __('app.active') : __('app.inactive') }}
                            </x-ui.badge>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-slate-500">
                            <span dir="ltr">{{ $user->email ?: '—' }}</span>
                            <span dir="ltr">{{ $user->mobile ?: '—' }}</span>
                        </div>
                    </div>
                </div>

                <div class="text-sm text-slate-500">
                    {{ __('identity::messages.created_at') }}: <span class="font-semibold text-slate-700">{{ $user->created_at?->format('Y/m/d') }}</span>
                </div>
            </div>
        </x-ui.card>

        <div class="flex gap-1 overflow-x-auto border-b border-slate-200" role="tablist">
            @foreach([
                'overview' => __('identity::messages.overview'),
                'general' => __('identity::messages.general_info'),
                'contact' => __('identity::messages.contact_info'),
                'account' => __('identity::messages.account_settings'),
                'projects' => __('identity::messages.user_projects'),
            ] as $key => $label)
                <button
                    type="button"
                    wire:click="selectTab('{{ $key }}')"
                    @class([
                        'whitespace-nowrap border-b-2 px-4 py-3 text-sm font-semibold transition',
                        'border-slate-950 text-slate-950' => $tab === $key,
                        'border-transparent text-slate-500 hover:text-slate-800' => $tab !== $key,
                    ])
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        @if($tab === 'overview')
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <x-ui.card>
                    <p class="text-sm font-semibold text-slate-500">{{ __('app.projects') }}</p>
                    <p class="mt-2 text-3xl font-black text-slate-950">{{ $projects->count() }}</p>
                </x-ui.card>
                <x-ui.card>
                    <p class="text-sm font-semibold text-slate-500">{{ __('identity::messages.all_tasks') }}</p>
                    <p class="mt-2 text-3xl font-black text-slate-950">{{ $taskCount }}</p>
                </x-ui.card>
                <x-ui.card>
                    <p class="text-sm font-semibold text-slate-500">{{ __('identity::messages.open_tasks') }}</p>
                    <p class="mt-2 text-3xl font-black text-slate-950">{{ $openTaskCount }}</p>
                </x-ui.card>
                <x-ui.card>
                    <p class="text-sm font-semibold text-slate-500">{{ __('identity::messages.done_tasks') }}</p>
                    <p class="mt-2 text-3xl font-black text-slate-950">{{ $doneTaskCount }}</p>
                </x-ui.card>
            </div>

            <div class="grid gap-5 xl:grid-cols-2">
                <x-ui.card>
                    <div class="space-y-5">
                        <h3 class="text-base font-bold text-slate-950">{{ __('identity::messages.profile_summary') }}</h3>

                        <dl class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-semibold text-slate-500">{{ __('app.name_label') }}</dt>
                                <dd class="mt-1 font-semibold text-slate-900">{{ $user->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold text-slate-500">{{ __('app.last_name') }}</dt>
                                <dd class="mt-1 font-semibold text-slate-900">{{ $user->last_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold text-slate-500">{{ __('app.email') }}</dt>
                                <dd dir="ltr" class="mt-1 text-right font-semibold text-slate-900">{{ $user->email ?: '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold text-slate-500">{{ __('app.mobile') }}</dt>
                                <dd dir="ltr" class="mt-1 text-right font-semibold text-slate-900">{{ $user->mobile ?: '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                </x-ui.card>

                <x-ui.card>
                    <div class="space-y-4">
                        <h3 class="text-base font-bold text-slate-950">{{ __('identity::messages.profile_completion') }}</h3>

                        <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-3">
                            <span class="text-sm font-semibold text-slate-700">{{ __('identity::messages.general_info') }}</span>
                            <x-ui.badge tone="success">{{ __('identity::messages.completed') }}</x-ui.badge>
                        </div>
                        <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-3">
                            <span class="text-sm font-semibold text-slate-700">{{ __('identity::messages.contact_info') }}</span>
                            <x-ui.badge :tone="filled($user->email) ? 'success' : 'neutral'">
                                {{ filled($user->email) ? __('identity::messages.completed') : __('identity::messages.incomplete') }}
                            </x-ui.badge>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-sm font-semibold text-slate-700">{{ __('identity::messages.account_settings') }}</span>
                            <x-ui.badge :tone="$hasPassword ? 'success' : 'neutral'">
                                {{ $hasPassword ? __('identity::messages.completed') : __('identity::messages.incomplete') }}
                            </x-ui.badge>
                        </div>
                    </div>
                </x-ui.card>
            </div>

            <x-ui.card>
                <div class="mb-4 flex items-center justify-between gap-4">
                    <h3 class="text-base font-bold text-slate-950">{{ __('identity::messages.user_projects') }}</h3>
                    @if($projects->isNotEmpty())
                        <button type="button" wire:click="selectTab('projects')" class="text-sm font-semibold text-slate-600 hover:text-slate-950">
                            {{ __('identity::messages.view_all') }}
                        </button>
                    @endif
                </div>

                @if($projects->isEmpty())
                    <p class="text-sm text-slate-500">{{ __('identity::messages.no_user_projects') }}</p>
                @else
                    <x-ui.table>
                        <thead>
                            <tr>
                                <th>{{ __('app.projects') }}</th>
                                <th>{{ __('app.tasks') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects->take(5) as $project)
                                <tr wire:key="overview-project-{{ $project->id }}">
                                    <td class="font-semibold text-slate-900">{{ $project->title }}</td>
                                    <td>{{ $projectTaskCounts[$project->id] ?? 0 }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-ui.table>
                @endif
            </x-ui.card>
        @elseif($tab === 'general')
            <form class="max-w-3xl" wire:submit="saveGeneral">
                <x-ui.card>
                    <div class="space-y-5">
                        <div>
                            <h3 class="text-base font-bold text-slate-950">{{ __('identity::messages.general_info') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('identity::messages.general_info_hint') }}</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.input name="name" :label="__('app.name_label')" :value="$name" wire:model="name" required />
                            <x-ui.input name="last_name" :label="__('app.last_name')" :value="$last_name" wire:model="last_name" required />
                        </div>

                        <x-ui.form-actions>
                            <x-ui.button type="submit" icon="fa-floppy-disk" wire:loading.attr="disabled" wire:target="saveGeneral">{{ __('app.save') }}</x-ui.button>
                        </x-ui.form-actions>
                    </div>
                </x-ui.card>
            </form>
        @elseif($tab === 'contact')
            <form class="max-w-3xl" wire:submit="saveContact">
                <x-ui.card>
                    <div class="space-y-5">
                        <div>
                            <h3 class="text-base font-bold text-slate-950">{{ __('identity::messages.contact_info') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('identity::messages.contact_info_hint') }}</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.input name="email" :label="__('app.email')" type="email" dir="ltr" :value="$email" wire:model="email" />
                            <x-ui.input name="mobile" :label="__('app.mobile')" dir="ltr" :value="$mobile" wire:model="mobile" />
                        </div>

                        <x-ui.form-actions>
                            <x-ui.button type="submit" icon="fa-floppy-disk" wire:loading.attr="disabled" wire:target="saveContact">{{ __('app.save') }}</x-ui.button>
                        </x-ui.form-actions>
                    </div>
                </x-ui.card>
            </form>
        @elseif($tab === 'account')
            <form class="max-w-3xl" wire:submit="saveAccount">
                <x-ui.card>
                    <div class="space-y-5">
                        <div>
                            <h3 class="text-base font-bold text-slate-950">{{ __('identity::messages.account_settings') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('identity::messages.account_settings_hint') }}</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.input name="password" :label="__('app.password')" type="password" wire:model="password" :hint="__('identity::messages.leave_password_blank')" />
                            <x-ui.input name="password_confirmation" :label="__('identity::messages.password_confirmation')" type="password" wire:model="password_confirmation" />
                        </div>

                        <div>
                            <x-ui.checkbox name="is_active" :label="__('identity::messages.is_active')" model="is_active" />
                            @error('is_active')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <x-ui.form-actions>
                            <x-ui.button type="submit" icon="fa-floppy-disk" wire:loading.attr="disabled" wire:target="saveAccount">{{ __('app.save') }}</x-ui.button>
                        </x-ui.form-actions>
                    </div>
                </x-ui.card>
            </form>
        @elseif($tab === 'projects')
            <x-ui.card>
                <div class="mb-5">
                    <h3 class="text-base font-bold text-slate-950">{{ __('identity::messages.user_projects') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('identity::messages.user_projects_hint') }}</p>
                </div>

                @if($projects->isEmpty())
                    <p class="text-sm text-slate-500">{{ __('identity::messages.no_user_projects') }}</p>
                @else
                    <x-ui.table>
                        <thead>
                            <tr>
                                <th>{{ __('app.title') }}</th>
                                <th>{{ __('app.tasks') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $project)
                                <tr wire:key="project-{{ $project->id }}">
                                    <td>
                                        <div class="font-semibold text-slate-900">{{ $project->title }}</div>
                                        @if($project->description)
                                            <div class="mt-1 max-w-2xl text-xs text-slate-500">{{ $project->description }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $projectTaskCounts[$project->id] ?? 0 }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-ui.table>
                @endif
            </x-ui.card>
        @endif
    </div>
</div>
