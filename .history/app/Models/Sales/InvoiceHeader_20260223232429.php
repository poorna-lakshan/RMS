<?php

namespace App\Models\Sales;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Inventory\Warehouse;

class InvoiceHeader extends Model
{
    use HasFactory;

    protected $table = 'invoice_header';
    protected $fillable = [
        'reference_no',
        'ware_house_id',
        'customer_id',
        'order_code',
        'user',
        'category',
        'payment_method',
        'item_count',
        'gross_total',
        'dis_per',
        'dis_total',
        'vat_per',
        'vat_total',
        'nbt_per',
        'nbt_total',
        'service_charge_per',
        'service_charge_total',
        'delivery_charge',
        'net_total',
        'total_cost',
        'credit_total',
        'paid_total',
        'balance',
        'is_full_pay',
        'is_void',
        'created_at'
    ];


      protected $casts = [
        'is_full_pay' => 'integer',
        'is_void' => 'integer',
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
        return $this->belongsTo(User::class, 'user');
    }


    public function details()
    {
        return $this->hasMany(InvoiceDetail::class, 'reference_no', 'id');
    }

    public function payments()
    {
        return $this->hasMany(InvPaymentMethod::class, 'trans_id', 'id');
    }
}
