<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdjustmentHeader extends Model
{
    use HasFactory;
    protected $table = 'adjustment_header';
    protected $fillable = ['code', 'date', 'ware_house_id','description','total_cost', 'net_total', 'user',
    'is_void'];

    public function details()
    {
        return $this->hasMany(AdjustmentDetail::class, 'adjustment_id');
    }

    public function from_warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_ware_house_id');
    }

     public function to_warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_ware_house_id');
    }
}
