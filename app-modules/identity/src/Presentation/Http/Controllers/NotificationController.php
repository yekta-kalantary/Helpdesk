<?php

namespace Modules\Identity\Presentation\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = auth()->user();

        return view('identity::notifications.index', [
            'notifications' => $user->notifications()->latest()->paginate(25),
        ]);
    }

    public function open(string $notification): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $item = $user->notifications()->findOrFail($notification);
        $item->markAsRead();

        return match ($item->data['kind'] ?? null) {
            'task_assigned' => redirect()->route('tasks.show', $item->data['task_id']),
            'ticket_reply' => redirect()->route('tickets.show', $item->data['ticket_id']),
            default => redirect()->route('notifications.index'),
        };
    }

    public function markAllRead(): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();

        return back()->with('success', __('identity::notifications.marked_read'));
    }
}
