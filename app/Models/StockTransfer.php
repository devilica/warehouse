<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransfer extends Model
{
    use HasFactory;
    
    protected $fillable = ['number', 'from_warehouse_id', 'to_warehouse_id', 'status', 'notes', 'approved_at', 'approved_by', 'shipped_at', 'received_at', 'completed_at'];

    public function fromWarehouse() { return $this->belongsTo(Warehouse::class, 'from_warehouse_id'); }
    public function toWarehouse() { return $this->belongsTo(Warehouse::class, 'to_warehouse_id'); }
    public function items() { return $this->hasMany(StockTransferItem::class); }
}