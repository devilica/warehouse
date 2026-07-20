<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockMovementRecorded
{
    use Dispatchable, SerializesModels;

    public function __construct(public mixed $product_id, public mixed $warehouse_id, public mixed $quantity, public mixed $type)
    {
        $this->product_id = $product_id;
        $this->warehouse_id = $warehouse_id;
        $this->quantity = $quantity;
        $this->type = $type;
    }
}