<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockTransferCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public mixed $stockTransfer)
    {
        $this->stockTransfer = $stockTransfer;
    }
}