<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemClass extends Model
{
    use HasFactory;

    protected $table = 'item_class';

    protected $fillable = [
        'description'
    ];
}
