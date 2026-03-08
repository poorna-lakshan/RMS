<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Inventory\Warehouse;
class PayHeader extends Model
{
    use HasFactory;

    protected $table = 'sup_pay_header';

    protected $fillable = [
        'code',
        'date',
        'vendor_id',
        'ware_house_id',
        'payment_method_id',
        'gl_acc',
        'is_confirm',
        'is_void',
    ];

     protected $casts = [
        'date' => 'date',
        'is_confirm' => 'boolean',
        'is_void' => 'boolean',
    ];

    // Relations
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'ware_house_id');
    }

    public function details()
    {
        return $this->hasMany(PayDetail::class, 'pay_id');
    }
}
