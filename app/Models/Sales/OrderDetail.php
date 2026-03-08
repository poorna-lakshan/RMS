<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Inventory\Item;
class OrderDetail extends Model
{
    use HasFactory;

      protected $table = 'order_detail';
      protected $fillable = [
        'order_id',
        'item_id',
        'comment',
        'qty',
        'unit_price',
        'status',
        'is_void',
        'void_reason',
    ];

    public function orderHeader()
    {
        return $this->belongsTo(OrderHeader::class, 'order_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
