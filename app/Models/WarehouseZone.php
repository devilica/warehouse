<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseZone extends Model
{
    use HasFactory;
    
    protected $fillable = ['warehouse_id', 'name', 'code'];

    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function shelves() { return $this->hasMany(WarehouseShelf::class); }
}