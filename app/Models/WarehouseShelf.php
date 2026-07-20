<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseShelf extends Model
{
    use HasFactory;
    
    protected $fillable = ['warehouse_zone_id', 'name', 'code'];

    public function zone() { return $this->belongsTo(WarehouseZone::class, 'warehouse_zone_id'); }
    public function locations() { return $this->hasMany(WarehouseLocation::class); }
}