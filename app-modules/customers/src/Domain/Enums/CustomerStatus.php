<?php

namespace Modules\Customers\Domain\Enums;

enum CustomerStatus: string
{
    case Lead = 'lead';
    case Active = 'active';
    case Inactive = 'inactive';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
