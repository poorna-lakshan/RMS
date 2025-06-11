<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customer';

    protected $fillable = [
        'code',
        'name',
        'contact',
        'address1',
        'address2',
        'email',
        'vat_no',
        'type',
        'custom1',
        'custom2',
        'custom3',
        'custom4',
        'custom5',
    ];
}
