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

    public function markAllRead(): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();

        return back()->with('success', __('identity::notifications.marked_read'));
    }
}
