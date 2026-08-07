<?php

namespace Modules\Tickets\Application\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Tickets\Domain\Contracts\TicketAttachmentStore;
use Modules\Tickets\Domain\Contracts\TicketNotifier;
use Modules\Tickets\Domain\Contracts\TicketRepository;

class ReplyTicket
{
    public function __construct(
        private readonly TicketRepository $tickets,
        private readonly TicketAttachmentStore $attachments,
        private readonly TicketNotifier $notifier,
    ) {
    }

    public function execute(int $ticketId, int $userId, string $body, array $files, bool $customerActor, string $subject): void
    {
        DB::transaction(function () use ($ticketId, $userId, $body, $files, $customerActor, $subject): void {
            $messageId = $this->tickets->addMessage($ticketId, $userId, $body);
            if ($files !== []) {
                $this->attachments->add($messageId, $files);
            }

            $this->tickets->updateStatus($ticketId, $customerActor ? 'open' : 'answered');
            $this->notifier->replied($ticketId, $userId, $customerActor, $subject);
        });
    }
}
