<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BOMDetail extends Model
{
    use HasFactory;

    protected $table = 'bom_detail';
    protected $fillable = ['line_no', 'bom_id', 'item_id', 'base_uom_id','base_qty', 'qty', 'is_alternative'];

    public function header()
    {
        return $this->belongsTo(BOMHeader::class, 'bom_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function uom()
    {
       return $this->belongsTo(ItemUOM::class, 'base_uom_id');
    }

    public function alternatives()
    {
        return $this->hasMany(BOMAlternative::class, 'bom_detail_id');
    }
}
