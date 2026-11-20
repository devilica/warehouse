<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransferItem extends Model
{
    use HasFactory;
    
    protected $fillable = ['stock_transfer_id', 'product_id', 'from_location_id', 'to_location_id', 'quantity_requested', 'quantity_shipped', 'quantity_received'];

    public function product() { return $this->belongsTo(Product::class); }
    public function fromLocation() { return $this->belongsTo(WarehouseLocation::class, 'from_location_id'); }
    public function toLocation() { return $this->belongsTo(WarehouseLocation::class, 'to_location_id'); }
}