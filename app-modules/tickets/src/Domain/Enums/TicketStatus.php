<?php

namespace Modules\Tickets\Domain\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case Pending = 'pending';
    case Answered = 'answered';
    case Closed = 'closed';
}
