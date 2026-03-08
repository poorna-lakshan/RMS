<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemKitchen extends Model
{
    use HasFactory;

    protected $table = 'item_kitchen';

    protected $fillable = [
        'item_id',
        'kitchen_id ',
    ];
}
