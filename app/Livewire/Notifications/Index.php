<?php

namespace App\Livewire\Notifications;

use Illuminate\Notifications\DatabaseNotification;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Tasks\Infrastructure\Models\Task;

class Index extends Component
{
    use WithPagination;

    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->refreshUnreadCount();
    }

    public function markAllRead(): void
    {
        /** @var User $user */
        $user = auth()->user();
        $user->unreadNotifications()->update(['read_at' => now()]);
        $this->refreshUnreadCount();
    }

    public function open(string $id)
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var DatabaseNotification $notification */
        $notification = $user->notifications()->whereKey($id)->firstOrFail();
        $type = $notification->data['resource_type'] ?? null;
        $resourceId = (int) ($notification->data['resource_id'] ?? 0);

        $route = match ($type) {
            'task' => $this->authorizedTaskRoute($user, $resourceId),
            'project' => $this->authorizedProjectRoute($user, $resourceId),
            default => null,
        };

        abort_unless($route, 404);
        $notification->markAsRead();
        $this->refreshUnreadCount();

        return redirect()->to($route);
    }

    private function refreshUnreadCount(): void
    {
        /** @var User $user */
        $user = auth()->user();
        $this->unreadCount = $user->unreadNotifications()->count();
    }

    private function authorizedTaskRoute(User $user, int $taskId): ?string
    {
        $task = Task::query()->visibleTo($user)->find($taskId);

        return $task ? route('tasks.show', $task) : null;
    }

    private function authorizedProjectRoute(User $user, int $projectId): ?string
    {
        $project = Project::query()->visibleTo($user)->find($projectId);

        return $project ? route('projects.show', $project) : null;
    }

    public function render()
    {
        /** @var User $user */
        $user = auth()->user();
        $notifications = $user->notifications()->latest()->paginate(20);

        return view('livewire.notifications.index', compact('notifications'))->title('اعلان‌ها');
    }
}
