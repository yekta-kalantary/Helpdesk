<?php

namespace Modules\Tasks\Domain\Enums;

enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case WaitingAdmin = 'waiting_admin';
    case WaitingCustomer = 'waiting_customer';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled], true);
    }
}
