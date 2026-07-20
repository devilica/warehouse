<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaFile extends Model
{
    use HasFactory;
    
    protected $fillable = ['fileable_type', 'fileable_id', 'path', 'mime_type', 'size'];

    
}