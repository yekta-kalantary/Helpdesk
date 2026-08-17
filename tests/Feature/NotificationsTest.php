<?php

use App\Livewire\Notifications\Index;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;

function createUnreadNotification(User $user, array $data = []): DatabaseNotification
{
    return $user->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'test',
        'data' => [
            'title' => 'Test notification',
            'body' => 'Test body',
            ...$data,
        ],
    ]);
}

it('shows the authenticated users unread notification count', function (): void {
    $user = User::factory()->admin()->create();
    $otherUser = User::factory()->admin()->create();
    createUnreadNotification($user);
    createUnreadNotification($user);
    createUnreadNotification($otherUser);

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->assertSet('unreadCount', 2)
        ->assertSee('2');
});

it('renders notification rows as readable links with unread emphasis', function (): void {
    $user = User::factory()->admin()->create();
    $notification = createUnreadNotification($user, ['title' => 'Review task', 'body' => 'A task needs your attention.']);

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->assertSee('Review task')
        ->assertSee('A task needs your attention.')
        ->assertSee('خوانده‌نشده')
        ->assertSee('border-y', false)
        ->assertSee("wire:click=\"open('{$notification->id}')\"", false);
});

it('caps large unread counts for a usable notification badge', function (): void {
    $user = User::factory()->admin()->create();

    foreach (range(1, 100) as $number) {
        createUnreadNotification($user, ['title' => "Notification {$number}"]);
    }

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->assertSet('unreadCount', 100)
        ->assertSee('99+');
});

it('updates the unread count after opening a notification', function (): void {
    $client = Client::factory()->create();
    $user = User::factory()->admin()->create();
    $project = mvpProject($client);
    $notification = createUnreadNotification($user, [
        'resource_type' => 'project',
        'resource_id' => $project->id,
    ]);

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->call('open', $notification->id)
        ->assertSet('unreadCount', 0);

    expect($notification->refresh()->read_at)->not->toBeNull();
});

it('updates the unread count after marking all notifications as read', function (): void {
    $user = User::factory()->admin()->create();
    createUnreadNotification($user);
    createUnreadNotification($user);

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->call('markAllRead')
        ->assertSet('unreadCount', 0);

    expect($user->unreadNotifications()->count())->toBe(0);
});
