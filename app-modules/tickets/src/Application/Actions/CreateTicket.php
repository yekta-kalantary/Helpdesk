<?php

namespace Modules\Tickets\Application\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Tickets\Domain\Contracts\TicketAttachmentStore;
use Modules\Tickets\Domain\Contracts\TicketNotifier;
use Modules\Tickets\Domain\Contracts\TicketRepository;

class CreateTicket
{
    public function __construct(
        private readonly TicketRepository $tickets,
        private readonly TicketAttachmentStore $attachments,
        private readonly TicketNotifier $notifier,
    ) {}

    public function execute(array $attributes, int $userId, string $body, array $files, bool $customerActor): int
    {
        return DB::transaction(function () use ($attributes, $userId, $body, $files, $customerActor): int {
            $ticketId = $this->tickets->create($attributes);
            $messageId = $this->tickets->addMessage($ticketId, $userId, $body);
            if ($files !== []) {
                $this->attachments->add($messageId, $files);
            }
            $this->notifier->replied($ticketId, $userId, $customerActor, $attributes['subject']);

            return $ticketId;
        });
    }
}
