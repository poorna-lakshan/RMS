<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferDetail extends Model
{
    use HasFactory;

    use HasFactory;
    protected $table = 'transfer_detail';
    protected $fillable = ['transfer_id', 'line_no', 'item_id','from_qoh','to_qoh', 'qty', 'unit_cost',
    'price_level1','price_level2','price_level3','line_cost','line_price_level1','is_void'];

    public function items()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function header()
    {
        return $this->belongsTo(TransferHeader::class, 'transfer_id');
    }

}
