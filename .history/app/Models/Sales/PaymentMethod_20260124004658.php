<?php

namespace App\Models\sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $table = 'payment_method';

    protected $fillable = [
        'description',
        'charge',
        'is_sync',
    ];

    
     protected $casts = [
        'is_sync' => 'boolean',
    ];
}
