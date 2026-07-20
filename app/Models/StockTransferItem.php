<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransferItem extends Model
{
    use HasFactory;
    
    protected $fillable = ['stock_transfer_id', 'product_id', 'from_warehouse_location_id', 'to_warehouse_location_id', 'quantity'];

    public function product() { return $this->belongsTo(Product::class); }
}