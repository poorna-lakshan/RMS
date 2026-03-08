<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BOMAlternative extends Model
{
    use HasFactory;

    protected $table = 'bom_alternatives';
    protected $fillable = ['bom_detail_id', 'parent_item_id', 'item_id', 'base_uom_id','base_qty', 'qty'];

    public function detail()
    {
        return $this->belongsTo(BOMDetail::class, 'bom_detail_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function uom()
    {
       return $this->belongsTo(ItemUOM::class, 'base_uom_id');
    }
    public function parentItem()
    {
        return $this->belongsTo(Item::class, 'parent_item_id');
    }
}
