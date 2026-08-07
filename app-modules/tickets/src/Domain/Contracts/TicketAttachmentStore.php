<?php

namespace Modules\Tickets\Domain\Contracts;

interface TicketAttachmentStore
{
    public function add(int $messageId, array $files): void;

    /** @return array{name:string,path:string,mime_type:?string} */
    public function get(int $ticketId, int $messageId, int $mediaId): array;
}
