<?php

namespace Modules\Tasks\Presentation\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Tasks\Infrastructure\Models\Attachment;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AttachmentDownloadController
{
    public function __invoke(Attachment $attachment): BinaryFileResponse|Response
    {
        return $this->download($attachment);
    }

    public function preview(Attachment $attachment): BinaryFileResponse|Response
    {
        abort_unless(Gate::allows('view', $attachment), 404);
        abort_unless($attachment->isPreviewable(), 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($attachment->storage_path), 404);

        return response()->file(
            $disk->path($attachment->storage_path),
            [
                'Content-Type' => $attachment->mime_type,
                'Content-Disposition' => 'inline; filename='.$this->safeFilename($attachment->original_name),
                'X-Content-Type-Options' => 'nosniff',
                'Content-Security-Policy' => "sandbox; default-src 'none'",
                'Cache-Control' => 'private, no-store',
                'Referrer-Policy' => 'no-referrer',
            ],
        );
    }

    private function download(Attachment $attachment): BinaryFileResponse|Response
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

    private function safeFilename(string $filename): string
    {
        $filename = Str::ascii(basename($filename));
        $filename = preg_replace('/[^A-Za-z0-9._-]+/', '-', $filename) ?: '';
        $filename = trim($filename, '.-');

        return $filename !== '' ? $filename : 'attachment';
    }
}
