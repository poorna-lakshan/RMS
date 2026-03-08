<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
