<?php

namespace Modules\Tickets\Domain\Contracts;

interface TicketNotifier
{
    public function replied(int $ticketId, int $actorId, bool $customerActor, string $subject): void;
}
