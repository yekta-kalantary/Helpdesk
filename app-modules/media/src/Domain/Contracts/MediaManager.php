<?php

namespace Modules\Media\Domain\Contracts;

interface MediaManager
{
    /** @param array<int, mixed> $files */
    public function add(string $modelType, int $modelId, string $collection, array $files, string $disk = 'local'): void;

    /** @return array<int, array{id:int,name:string,size:int,mime_type:?string}> */
    public function list(string $modelType, int $modelId, string $collection): array;

    /** @return array{name:string,path:string,mime_type:?string} */
    public function get(string $modelType, int $modelId, int $mediaId): array;

    public function delete(string $modelType, int $modelId, int $mediaId): void;
}
