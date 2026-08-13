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
        <div class="grid w-full gap-3 md:grid-cols-3">
            <x-ui.input name="q" :value="$q" wire:model.live.debounce.300ms="q" :placeholder="__('identity::messages.search_users')" />
            <x-ui.select name="client" wire:model.live="client">
                <option value="">همه مشتریان</option>
                @foreach($clients as $clientItem)
                    <option value="{{ $clientItem->id }}">{{ $clientItem->name }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.select name="status" wire:model.live="status">
                <option value="">همه وضعیت‌ها</option>
                <option value="active">فعال</option>
                <option value="inactive">غیرفعال</option>
            </x-ui.select>
        </div>
    </x-ui.filter-bar>

    <div class="overflow-x-auto">
        <x-ui.table wire:loading.class="opacity-60" wire:target="q,client,status">
            <thead>
                <tr>
                    <th>نام</th>
                    <th>ایمیل / موبایل</th>
                    <th>مشتری</th>
                    <th>آخرین ورود</th>
                    <th>{{ __('app.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr wire:key="user-{{ $user->id }}">
                        <td><a href="{{ route('users.show', $user) }}" wire:navigate class="font-bold text-slate-950 hover:underline">{{ $user->full_name }}</a></td>
                        <td>
                            <div dir="ltr" class="text-right">{{ $user->email }}</div>
                            @if($user->mobile)<div dir="ltr" class="mt-1 text-right text-xs text-slate-500">{{ $user->mobile }}</div>@endif
                        </td>
                        <td>{{ $user->client?->name ?? '—' }}</td>
                        <td><x-ui.date :value="$user->last_login_at" datetime />{{ $user->last_login_at ? '' : '—' }}</td>
                        <td><x-ui.badge :tone="$user->is_active ? 'success' : 'neutral'">{{ $user->is_active ? 'فعال' : 'غیرفعال' }}</x-ui.badge></td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="5" />
                @endforelse
            </tbody>
        </x-ui.table>
    </div>

    <div class="mt-5">{{ $users->links() }}</div>
</div>
