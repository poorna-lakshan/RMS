<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferHeader extends Model
{
    use HasFactory;
    protected $table = 'transfer_header';
    protected $fillable = ['code', 'date', 'from_ware_house_id','to_ware_house_id','description', 'total_cost', 'net_total',
    'user','is_void'];

    public function details()
    {
        return $this->hasMany(TransferDetail::class, 'transfer_id');
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
