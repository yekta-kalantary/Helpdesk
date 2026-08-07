<?php

namespace Modules\Tickets\Infrastructure;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Tickets\Domain\Contracts\TicketNotifier;
use Modules\Tickets\Infrastructure\Notifications\TicketReplyNotification;

class DatabaseTicketNotifier implements TicketNotifier
{
    public function replied(int $ticketId, int $actorId, bool $customerActor, string $subject): void
    {
        $ticket = DB::table('tickets')->where('id', $ticketId)->first(['customer_id', 'assigned_to']);
        if (! $ticket) {
            return;
        }

        if ($customerActor) {
            if ($ticket->assigned_to && (int) $ticket->assigned_to !== $actorId) {
                User::query()->find($ticket->assigned_to)?->notify(new TicketReplyNotification($ticketId, $subject));
                return;
            }

            $admins = User::query()->role('admin')->get();
            $managers = User::query()->permission('tickets.manage_all')->get();
            $admins->merge($managers)->unique('id')->reject(fn (User $user) => $user->id === $actorId)
                ->each(fn (User $user) => $user->notify(new TicketReplyNotification($ticketId, $subject)));
            return;
        }

        $customerUserId = DB::table('customers')->where('id', $ticket->customer_id)->value('user_id');
        if ($customerUserId && (int) $customerUserId !== $actorId) {
            User::query()->find($customerUserId)?->notify(new TicketReplyNotification($ticketId, $subject));
        }
    }
}
