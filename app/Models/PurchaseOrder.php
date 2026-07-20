<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory;
    
    protected $fillable = ['number', 'supplier_id', 'status', 'expected_delivery_date', 'created_by', 'sent_at', 'closed_at'];

    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function items() { return $this->hasMany(PurchaseOrderItem::class); }
    public function goodsReceipts() { return $this->hasMany(GoodsReceipt::class); }
}