<?php

namespace Modules\Tickets\Infrastructure;

use Modules\Tickets\Domain\Contracts\TicketAttachmentStore;
use Modules\Tickets\Infrastructure\Models\TicketMessage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class LocalTicketAttachmentStore implements TicketAttachmentStore
{
    public function add(int $messageId, array $files): void
    {
        $message = TicketMessage::query()->findOrFail($messageId);
        foreach ($files as $file) {
            $message->addMedia($file)->toMediaCollection('attachments', 'local');
        }
    }

    public function get(int $ticketId, int $messageId, int $mediaId): array
    {
        $message = TicketMessage::query()->where('ticket_id', $ticketId)->findOrFail($messageId);
        $media = Media::query()->findOrFail($mediaId);
        abort_unless($media->model_type === TicketMessage::class && (int) $media->model_id === $message->id, 404);

        return [
            'name' => $media->file_name,
            'path' => $media->getPath(),
            'mime_type' => $media->mime_type,
        ];
    }
}
