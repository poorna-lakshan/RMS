<?php

namespace App\Models\sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashierSessions extends Model
{
    use HasFactory;

    protected $table = 'cashier_sessions';
    public $timestamps = false;
    protected $fillable = [
        'code',
        'in_time',
        'out_time',
        'user',
        'is_in',
        'is_out',
        'in_amt',
        'out_amt',
    ];

     protected $casts = [
        'is_in' => 'integer',
        'is_out' => 'integer',
    ];
}
