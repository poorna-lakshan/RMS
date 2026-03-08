<?php

namespace App\Models\Sales;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Inventory\Warehouse;
class OrderHeader extends Model
{
    use HasFactory;
    protected $table = 'order_header';
    protected $fillable = [
        'reference_no',
        'kot',
        'type',
        'category',
        'ware_house_id',
        'customer_id',
        'order_code',
        'table',
        'user',
        'steward_id',
        'is_void',
        'is_invoice',
        'invoice_id',
        'is_paid',
        'created_at',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'ware_house_id');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user', 'name');
    }

    public function steward()
    {
        return $this->belongsTo(Steward::class, 'steward_id');
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'id');
    }
}
