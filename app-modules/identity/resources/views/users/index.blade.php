<div>
    @if(session('success'))
        <x-ui.alert class="mb-5" tone="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.page-header :title="__('identity::messages.users')">
        <x-slot:actions>
            <x-ui.button :href="route('users.create')" icon="fa-user-plus" wire:navigate>{{ __('identity::messages.new_user') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.filter-bar :livewire="true">
        <div class="min-w-0 flex-1">
            <x-ui.input name="q" :value="$q" wire:model.live.debounce.300ms="q" :placeholder="__('identity::messages.search_users')" />
        </div>
    </x-ui.filter-bar>

    <x-ui.table wire:loading.class="opacity-60" wire:target="q">
        <thead>
            <tr>
                <th>{{ __('identity::messages.user') }}</th>
                <th>{{ __('identity::messages.contact_info') }}</th>
                <th>{{ __('app.projects') }}</th>
                <th>{{ __('app.status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr
                    wire:key="user-{{ $user->id }}"
                    class="cursor-pointer transition hover:bg-slate-50"
                    onclick="window.location.href='{{ route('users.show', $user->id) }}'"
                >
                    <td>
                        <a href="{{ route('users.show', $user->id) }}" wire:navigate class="font-bold text-slate-950 hover:underline">
                            {{ $user->full_name }}
                        </a>
                    </td>
                    <td>
                        <div dir="ltr" class="text-right">{{ $user->email ?: '—' }}</div>
                        @if($user->mobile)
                            <div dir="ltr" class="mt-1 text-right text-xs text-slate-500">{{ $user->mobile }}</div>
                        @endif
                    </td>
                    <td class="font-semibold">{{ $projectCounts[$user->id] ?? 0 }}</td>
                    <td>
                        <x-ui.badge :tone="$user->is_active ? 'success' : 'neutral'">
                            {{ $user->is_active ? __('app.active') : __('app.inactive') }}
                        </x-ui.badge>
                    </td>
                </tr>
            @empty
                <x-ui.empty-row colspan="4" />
            @endforelse
        </tbody>
    </x-ui.table>
</div>
