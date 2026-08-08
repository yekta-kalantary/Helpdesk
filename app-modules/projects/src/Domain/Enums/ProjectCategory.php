<?php

namespace Modules\Projects\Domain\Enums;

enum ProjectCategory: string
{
    case Customer = 'customer';
    case Internal = 'internal';
}
