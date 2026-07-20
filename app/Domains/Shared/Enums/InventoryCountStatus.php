<?php

namespace App\Domains\Shared\Enums;

enum InventoryCountStatus: string
{
    case Scheduled = 'scheduled';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
