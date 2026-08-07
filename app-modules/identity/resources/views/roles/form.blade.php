@extends('layouts.app')

@section('title', $role ? __('identity::messages.edit_role') : __('identity::messages.new_role'))

@section('content')
    @php($pageTitle = $role ? __('identity::messages.edit_role') : __('identity::messages.new_role'))
    <x-ui.page-header :title="$pageTitle" />

    <form class="max-w-5xl" method="POST" action="{{ $role ? route('roles.update', $role['id']) : route('roles.store') }}">
        @csrf
        @if($role) @method('PUT') @endif

        <x-ui.card>
            <div class="space-y-5">
                <div class="max-w-md"><x-ui.input name="name" :label="__('identity::messages.role')" dir="ltr" :value="$role['name'] ?? ''" required /></div>

                <div>
                    <div class="mb-4">
                        <div class="text-sm font-semibold text-slate-700">{{ __('identity::messages.permissions') }}</div>
                        <p class="mt-1 text-sm text-slate-500">{{ __('identity::messages.permission_selection_hint') }}</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach(collect($permissions)->groupBy('module') as $module => $modulePermissions)
                            <section class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <h2 class="mb-3 font-bold text-slate-900">{{ __('identity::messages.permission_modules.'.$module) }}</h2>
                                <div class="space-y-2">
                                    @foreach($modulePermissions as $permission)
                                        <x-ui.checkbox name="permissions[]" :label="$permission['name']" :value="$permission['name']" :checked="in_array($permission['name'], old('permissions', $role['permissions'] ?? []), true)" dir="ltr" />
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>
                </div>

                <x-ui.form-actions>
                    <x-ui.button type="submit">{{ __('app.save') }}</x-ui.button>
                    <x-ui.button variant="secondary" :href="route('roles.index')">{{ __('app.cancel') }}</x-ui.button>
                </x-ui.form-actions>
            </div>
        </x-ui.card>
    </form>
@endsection
