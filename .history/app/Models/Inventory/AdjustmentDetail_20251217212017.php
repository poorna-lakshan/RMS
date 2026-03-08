<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdjustmentDetail extends Model
{
    use HasFactory;



    use HasFactory;
    protected $table = 'adjustment_detail';
    protected $fillable = [
        'adjustment_id',
        'line_no',
        'reason',
        'item_id',
        'qoh',
        'qty',
        'unit_cost',
        'price_level1',
        'price_level2',
        'price_level3',
        'line_cost',
        'line_price_level1',
        'is_void'
    ];



    public function items()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
    public function header()
    {
        return $this->belongsTo(AdjustmentHeader::class, 'adjustment_foreignKey: id');
    }


    public function sku()
    {
        return $this->belongsTo(ItemSku::class, 'sku_id');
    }
}
