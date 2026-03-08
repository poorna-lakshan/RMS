<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BOMHeader extends Model
{
    use HasFactory;

    protected $table = 'bom_header';
    protected $fillable = ['code', 'date', 'ware_house_id', 'item_id', 'active'];

    public function details()
    {
        return $this->hasMany(BOMDetail::class, 'bom_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'ware_house_id');
    }

    public function finalItem()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
