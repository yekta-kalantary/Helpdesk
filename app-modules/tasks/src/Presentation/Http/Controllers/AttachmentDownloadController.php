<?php

namespace Modules\Tasks\Presentation\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Modules\Tasks\Infrastructure\Models\Attachment;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AttachmentDownloadController
{
    public function __invoke(Attachment $attachment): BinaryFileResponse|Response
    {
        abort_unless(Gate::allows('view', $attachment), 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($attachment->storage_path), 404);

        return response()->download(
            $disk->path($attachment->storage_path),
            $attachment->original_name,
            ['Content-Type' => $attachment->mime_type],
        );
    }
}
