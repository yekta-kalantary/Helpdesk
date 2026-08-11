<?php

namespace Modules\Clients\Domain\Enums;

enum ClientStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
