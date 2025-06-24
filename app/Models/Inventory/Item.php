<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $table = 'item';

    protected $fillable = [
        'code',
        'description',
        'class_id',
        'category_id',
        'uom',
        'costing_method',
        'vendor_id',
        'sales_acc',
        'cost_of_sales_acc',
        'inventory_acc',
        'unit_cost',
        'price_level1',
        'price_level2',
        'price_level3',
        'discount_amt',
        'discount_presentage',
        'reorder_qty',
        'minimum_qty',
        'barcode',
        'kot',
        'bot',
        'custom1',
        'custom2',
        'custom3',
        'custom4',
        'custom5',
        'image',
    ];

    public function warehouses()
{
    return $this->belongsToMany(Warehouse::class, 'item_ware_house', 'item_id', 'ware_house_id')
        ->withPivot('ware_house_id','qty', 'avg_cost')
        ->withTimestamps();
}
}
