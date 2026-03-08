<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemUom extends Model
{
    use HasFactory;

    protected $table = 'uom';

    protected $fillable = [
        'description'
    ];
}
