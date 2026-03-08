<?php

namespace App\Models\Purchasing;

use App\Models\Inventory\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class GRNHeader extends Model
{
    use HasFactory;

    protected $table = 'grn_header';

    protected $fillable = [
        'code',
        'date',
        'vendor_id',
        'ware_house_id',
        'sup_inv',
        'gl_acc',
        'total_line_discount',
        'discount_pra',
        'discount',
        'nbt',
        'vat',
        'gross_total',
        'net_total',
        'is_confirm',
        'is_void',
        'paid_amount'
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
        return $this->hasMany(GRNDetail::class, 'gen_id');
    }
    public function payments()
    {
        return $this->hasMany(PayDetail::class, 'grn_id');
    }
}
