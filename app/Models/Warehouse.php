<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'ap_account',
        'ar_account',
        'cash_account',
        'sales_account',
        'cost_of_sales_account',
        'inventory_account',
        'default_warehouse'
    ];
}
