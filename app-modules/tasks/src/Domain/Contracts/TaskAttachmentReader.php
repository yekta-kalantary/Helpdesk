<?php

namespace Modules\Tasks\Domain\Contracts;

interface TaskAttachmentReader
{
    /** @return array{name:string,path:string,mime_type:?string} */
    public function get(int $taskId, int $mediaId): array;
}
