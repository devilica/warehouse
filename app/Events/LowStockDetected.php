<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockDetected
{
    use Dispatchable, SerializesModels;

    public function __construct(public mixed $product_id, public mixed $warehouse_id, public mixed $current_quantity, public mixed $reorder_level)
    {
        $this->product_id = $product_id;
        $this->warehouse_id = $warehouse_id;
        $this->current_quantity = $current_quantity;
        $this->reorder_level = $reorder_level;
    }
}