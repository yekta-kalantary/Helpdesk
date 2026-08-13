<?php

namespace Modules\Projects\Domain\Enums;

enum ProjectStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
}
