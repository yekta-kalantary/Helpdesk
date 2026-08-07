<?php

namespace Modules\Tickets\Presentation\Http\Controllers;

use App\Models\User;
use Illuminate\Routing\Controller;
use Modules\Tickets\Application\Queries\TicketAccessScope;
use Modules\Tickets\Domain\Contracts\TicketAttachmentStore;
use Modules\Tickets\Domain\Contracts\TicketRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TicketAttachmentController extends Controller
{
    public function __construct(
        private readonly TicketRepository $tickets,
        private readonly TicketAccessScope $scopeBuilder,
        private readonly TicketAttachmentStore $attachments,
    ) {}

    public function __invoke(int $ticket, int $message, int $media): BinaryFileResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $scope = $this->scopeBuilder->for($user);

        $this->tickets->findAccessible($ticket, $scope);
        $file = $this->attachments->get($ticket, $message, $media);

        return response()->download(
            $file['path'],
            $file['name'],
            ['Content-Type' => $file['mime_type'] ?? 'application/octet-stream'],
        );
    }
}
