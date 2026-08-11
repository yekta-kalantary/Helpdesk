<?php

namespace Modules\Tasks\Presentation\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Modules\Identity\Infrastructure\Models\User;
use Modules\Tasks\Infrastructure\Models\Attachment;
use Modules\Tasks\Infrastructure\Models\Task;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AttachmentDownloadController
{
    public function __invoke(Attachment $attachment): BinaryFileResponse|Response
    {
        /** @var User $user */
        $user = auth()->user();

        abort_unless($user->is_active && $user->canAuthenticate(), 404);

        if (! $user->isAdmin()) {
            abort_if($attachment->hidden_at !== null, 404);
            abort_unless(Task::query()->visibleTo($user)->whereKey($attachment->task_id)->exists(), 404);
        }

        $disk = Storage::disk('local');
        abort_unless($disk->exists($attachment->storage_path), 404);

        return response()->download(
            $disk->path($attachment->storage_path),
            $attachment->original_name,
            ['Content-Type' => $attachment->mime_type],
        );
    }
}
