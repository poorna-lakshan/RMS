<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GRNDetail extends Model
{
    use HasFactory;

    protected $table = 'grn_detail';

    protected $fillable = [
        'gen_id',
        'item_id',
        'sku_id',
        'qoh',
        'qty',
        'discount_pra',
        'discount',
        'unit_cost',
        'price_level1',
        'price_level2',
        'price_level3',
        'free_qty',
        'total_cost',
        'expire_date'
    ];

    protected $casts = [
        'expire_date' => 'date',
    ];

    // Relations
    public function grnHeader()
    {
        return $this->belongsTo(GRNHeader::class, 'gen_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function sku()
    {
        return $this->belongsTo(Sku::class, 'sku_id');
    }
}
