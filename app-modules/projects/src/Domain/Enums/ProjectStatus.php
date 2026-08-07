<?php

namespace Modules\Projects\Domain\Enums;

enum ProjectStatus: string
{
    case Planning = 'planning';
    case Active = 'active';
    case Paused = 'paused';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
