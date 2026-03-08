<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemActivity extends Model
{
    use HasFactory;

    protected $table = 'item_activity';

    protected $fillable = [
        'doc_type',
        'ware_house_id',
        'reference_no',
        'date',
        'trans_type',
        'doc_reference',
        'item_id',
        'qty',
        'unit_cost',
        'retail_price',
        'qoh',
        'price_level1',
        'price_level2',
        'price_level3',
        'grn_no',
        'is_void',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'ware_house_id');
    }
}