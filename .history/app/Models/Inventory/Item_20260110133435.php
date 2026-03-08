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
        'sub_category',
        'type',
        'uom_id',
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
        'kitchen_id',
        'custom1',
        'custom2',
        'custom3',
        'custom4',
        'custom5',
        'image',
        'warehouses'
    ];

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'item_ware_house', 'item_id', 'ware_house_id')
            ->withPivot('ware_house_id', 'qty', 'avg_cost')
            ->withTimestamps();
    }



   public function skus()
    {
        return $this->hasMany(ItemSku::class, 'item_id');
    }

    // Item.php
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            // Make sure storage link exists: public/storage -> storage/app/public

            //return rtrim(config('app.url'), '/') . '/storage/app/public/' . ltrim($this->image, '/');//after host
            return asset('storage/' . $this->image);
        }
        return null;
    }

    public function uom()
    {
       return $this->belongsTo(ItemUOM::class, 'uom_id');
    }
    public function kitchans()
    {
       return $this->belongsTo(Kitchen::class, 'kitchen_id');
    }
    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }

    public function itemClass()
    {
        return $this->belongsTo(ItemClass::class, 'class_id');
    }


   
}

