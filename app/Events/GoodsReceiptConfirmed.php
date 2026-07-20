<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GoodsReceiptConfirmed
{
    use Dispatchable, SerializesModels;

    public function __construct(public mixed $goodsReceipt)
    {
        $this->goodsReceipt = $goodsReceipt;
    }
}