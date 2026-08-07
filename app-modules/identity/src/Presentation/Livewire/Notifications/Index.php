<?php

namespace Modules\Identity\Presentation\Livewire\Notifications;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function markAllRead()
    {
        abort_unless(auth()->user()?->can('notifications.view'), 403);

        /** @var User $user */
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();
        session()->flash('success', __('identity::notifications.marked_read'));

        return $this->redirectRoute('notifications.index', navigate: true);
    }

    public function open(string $notification)
    {
        abort_unless(auth()->user()?->can('notifications.view'), 403);

        /** @var User $user */
        $user = auth()->user();
        $item = $user->notifications()->findOrFail($notification);
        $item->markAsRead();

        return match ($item->data['kind'] ?? null) {
            'task_assigned' => $this->redirectRoute('tasks.show', ['task' => $item->data['task_id']], navigate: true),
            'ticket_reply' => $this->redirectRoute('tickets.show', ['ticket' => $item->data['ticket_id']], navigate: true),
            default => $this->redirectRoute('notifications.index', navigate: true),
        };
    }

    public function render()
    {
        /** @var User $user */
        $user = auth()->user();

        return view('identity::notifications.index', [
            'notifications' => $user->notifications()->latest()->paginate(25),
        ])->title(__('identity::notifications.title'));
    }
}
