<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierContact extends Model
{
    use HasFactory;
    
    protected $fillable = ['supplier_id', 'name', 'email', 'phone'];

    public function supplier() { return $this->belongsTo(Supplier::class); }
}