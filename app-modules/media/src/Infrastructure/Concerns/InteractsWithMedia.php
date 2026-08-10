<?php

namespace Modules\Media\Infrastructure\Concerns;

use Spatie\MediaLibrary\InteractsWithMedia as SpatieInteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait InteractsWithMedia
{
    use SpatieInteractsWithMedia;

    public function registerMediaConversions(?Media $media = null): void
    {
        // Shared default: keep originals only. Modules may override when conversions are required.
    }
}
