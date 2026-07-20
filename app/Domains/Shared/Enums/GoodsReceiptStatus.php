<?php

namespace App\Domains\Shared\Enums;

enum GoodsReceiptStatus: string
{
    case Draft = 'draft';
    case InProgress = 'in_progress';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
}
