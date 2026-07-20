<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsReceiptItem extends Model
{
    use HasFactory;
    
    protected $fillable = ['goods_receipt_id', 'purchase_order_item_id', 'product_id', 'warehouse_location_id', 'quantity_received', 'quantity_damaged'];

    public function product() { return $this->belongsTo(Product::class); }
    public function goodsReceipt() { return $this->belongsTo(GoodsReceipt::class); }
}