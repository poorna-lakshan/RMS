<?php

namespace App\Models\default;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
     protected $table = 'company';

    protected $fillable = [
        'name',
        'address1',
        'address2',
        'city',
        'state',
        'contry',
        'phone',
        'fax',
        'email',
        'website',
        'service_charge',
        'invoice_sms',
        'invoice_sms_template',
        'sms_api',
        'void_pin',

    ];
}
