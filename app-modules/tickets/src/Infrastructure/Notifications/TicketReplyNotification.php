<?php

namespace Modules\Tickets\Infrastructure\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketReplyNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly int $ticketId,
        private readonly string $subject,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'ticket_reply',
            'ticket_id' => $this->ticketId,
            'subject' => $this->subject,
            'message_key' => 'tickets::messages.notification.reply',
        ];
    }
}
