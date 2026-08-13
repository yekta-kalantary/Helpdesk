<?php

namespace Modules\Identity\Domain\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Customer = 'customer';
}
