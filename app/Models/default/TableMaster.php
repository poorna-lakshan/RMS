<?php

namespace App\Models\default;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableMaster extends Model
{
    use HasFactory;
     protected $table = 'table_master';

    protected $fillable = [
        'id',
        'category',
        'capacity',
        'name',
        'is_booking',
        'is_sync'
    ];

      protected $casts = [
        'is_sync' => 'boolean',
    ];
}
