<?php

namespace App\Domains\Shared\Enums;

enum StockTransferStatus: string
{
    case Draft = 'draft';
    case Approved = 'approved';
    case Shipped = 'shipped';
    case Received = 'received';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
