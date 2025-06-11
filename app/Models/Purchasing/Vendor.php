<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $table = 'vendor';

    protected $fillable = [
        'code',
        'name',
        'contact',
        'address1',
        'address2',
        'ref_name',
        'ref_contact',
        'company_name',
        'other_name',
        'custom1',
        'custom2',
        'custom3',
        'custom4',
        'custom5',
    ];
}
