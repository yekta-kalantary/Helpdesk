<?php

namespace Modules\Media\Infrastructure;

use Illuminate\Database\Eloquent\Model;
use Modules\Media\Domain\Contracts\MediaManager;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SpatieMediaManager implements MediaManager
{
    public function add(string $modelType, int $modelId, string $collection, array $files, string $disk = 'local'): void
    {
        $owner = $this->owner($modelType, $modelId);

        foreach ($files as $file) {
            $owner->addMedia($file)->toMediaCollection($collection, $disk);
        }
    }

    public function list(string $modelType, int $modelId, string $collection): array
    {
        $this->owner($modelType, $modelId);

        return Media::query()
            ->where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->where('collection_name', $collection)
            ->orderBy('order_column')
            ->orderBy('id')
            ->get()
            ->map(fn (Media $media): array => [
                'id' => $media->id,
                'name' => $media->file_name,
                'size' => (int) $media->size,
                'mime_type' => $media->mime_type,
            ])
            ->all();
    }

    public function get(string $modelType, int $modelId, int $mediaId): array
    {
        $media = $this->ownedMedia($modelType, $modelId, $mediaId);

        return [
            'name' => $media->file_name,
            'path' => $media->getPath(),
            'mime_type' => $media->mime_type,
        ];
    }

    public function delete(string $modelType, int $modelId, int $mediaId): void
    {
        $this->ownedMedia($modelType, $modelId, $mediaId)->delete();
    }

    private function owner(string $modelType, int $modelId): HasMedia
    {
        abort_unless(is_a($modelType, Model::class, true), 500);

        /** @var Model $model */
        $model = $modelType::query()->findOrFail($modelId);
        abort_unless($model instanceof HasMedia, 500);

        return $model;
    }

    private function ownedMedia(string $modelType, int $modelId, int $mediaId): Media
    {
        $this->owner($modelType, $modelId);

        return Media::query()
            ->whereKey($mediaId)
            ->where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->firstOrFail();
    }
}
