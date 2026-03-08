<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $table = 'ware_house';

    protected $fillable = [
        'code',
        'name',
        'address1',
        'address2',
        'city',
        'state',
        'contry',
        'phone',
        'fax',
        'ap_acc',
        'ar_acc',
        'cash_acc',
        'sales_acc',
        'cos_acc',
        'inv_acc',
        'price_level' 
    ];
}
