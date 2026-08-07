<?php

namespace Modules\Tickets\Domain\Enums;

enum TicketCategory: string
{
    case Technical = 'technical';
    case Seo = 'seo';
    case Design = 'design';
    case Content = 'content';
    case General = 'general';
}
