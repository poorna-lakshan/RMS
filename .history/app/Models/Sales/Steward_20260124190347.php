<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Steward extends Model
{
    use HasFactory;
      protected $table = 'steward';

    protected $fillable = [
        'id',
        'name',
        'is_sync'
    ];

     protected $casts = [
        'is_sync' => 'boolean',
    ];
}
