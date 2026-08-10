<?php

namespace Modules\Tasks\Presentation\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Tasks\Application\Queries\TaskAccessScope;
use Modules\Tasks\Domain\Contracts\TaskAttachmentReader;
use Modules\Tasks\Domain\Contracts\TaskRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TaskAttachmentController extends Controller
{
    public function __construct(
        private readonly TaskRepository $tasks,
        private readonly TaskAccessScope $scopeBuilder,
        private readonly TaskAttachmentReader $attachmentReader,
    ) {}

    public function __invoke(int $task, int $media): BinaryFileResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $scope = $this->scopeBuilder->for($user);

        $this->tasks->findAccessible($task, $scope);
        $file = $this->attachmentReader->get($task, $media);

        return response()->download(
            $file['path'],
            $file['name'],
            ['Content-Type' => $file['mime_type'] ?? 'application/octet-stream'],
        );
    }
}
