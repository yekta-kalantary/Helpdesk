@extends('layouts.app')

@section('title', $project ? __('projects::messages.edit_project') : __('projects::messages.new_project'))

@section('content')
    @php($pageTitle = $project ? __('projects::messages.edit_project') : __('projects::messages.new_project'))
    <x-ui.page-header :title="$pageTitle" />

    <form class="max-w-5xl" method="POST" action="{{ $project ? route('projects.update', $project['id']) : route('projects.store') }}">
        @csrf
        @if($project) @method('PUT') @endif

        <x-ui.card>
            <div class="space-y-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="title" :label="__('app.title')" :value="$project['title'] ?? ''" required />
                    <x-ui.select name="customer_id" :label="__('projects::messages.customer')" required>
                        <option value="">—</option>
                        @foreach($options['customers'] as $customer)<option value="{{ $customer['id'] }}" @selected((string) old('customer_id', $project['customer_id'] ?? '') === (string) $customer['id'])>{{ $customer['name'] }}</option>@endforeach
                    </x-ui.select>
                    <x-ui.select name="type" :label="__('projects::messages.type')" required>
                        @foreach($types as $type)<option value="{{ $type->value }}" @selected(old('type', $project['type'] ?? '') === $type->value)>{{ __('projects::messages.type.'.$type->value) }}</option>@endforeach
                    </x-ui.select>
                    <x-ui.select name="status" :label="__('app.status')" required>
                        @foreach($statuses as $status)<option value="{{ $status->value }}" @selected(old('status', $project['status'] ?? 'planning') === $status->value)>{{ __('projects::messages.status.'.$status->value) }}</option>@endforeach
                    </x-ui.select>
                    <x-ui.input name="starts_at" :label="__('projects::messages.starts_at')" type="date" dir="ltr" :value="$project['starts_at'] ?? ''" />
                    <x-ui.input name="ends_at" :label="__('projects::messages.ends_at')" type="date" dir="ltr" :value="$project['ends_at'] ?? ''" />
                </div>

                <x-ui.textarea name="description" :label="__('app.description')" :value="$project['description'] ?? ''" />

                <div>
                    <div class="mb-2 text-sm font-semibold text-slate-700">{{ __('projects::messages.members') }}</div>
                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($options['members'] as $member)
                            <x-ui.checkbox name="member_ids[]" :label="$member['name']" :value="$member['id']" :checked="in_array($member['id'], array_map('intval', old('member_ids', $project['member_ids'] ?? [])), true)" />
                        @endforeach
                    </div>
                </div>

                <x-ui.form-actions>
                    <x-ui.button type="submit">{{ __('app.save') }}</x-ui.button>
                    <x-ui.button variant="secondary" :href="route('projects.index')">{{ __('app.cancel') }}</x-ui.button>
                </x-ui.form-actions>
            </div>
        </x-ui.card>
    </form>
@endsection
