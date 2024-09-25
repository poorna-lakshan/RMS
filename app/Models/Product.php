<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'itemid', 'description', 'itemclass_id', 'active', 'inactive', 'itemcategory_id',
        'unit_cost', 'selling_price', 'uom', 'price2', 'price3', 'cost_method',
        'reorder_level', 'preferred_vendor_id', 'minimum_quantity', 'kot_check',
        'bot_check', 'barcode', 'warehouse', 'custom1', 'custom2', 'custom3',
        'custom4', 'custom5', 'image'
    ];
}
