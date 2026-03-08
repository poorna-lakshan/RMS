<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemSku extends Model
{
    use HasFactory;

    protected $table = 'item_sku';

    protected $fillable = [
        'item_id',
        'sku',
        'uom_id',
        'qty_per_sku',
    ];

    protected $casts = [
        'item_id' => 'integer',
        'uom_id' => 'integer',
    ];

    public function uom()
    {
        return $this->belongsTo(ItemUOM::class);
    }


}
