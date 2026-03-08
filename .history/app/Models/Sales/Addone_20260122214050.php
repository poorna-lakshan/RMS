<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Addone extends Model
{
    use HasFactory;

    protected $table = 'addone_master'; // Changed from 'expence_category'

    protected $fillable = [
        'addone',
        'unit_price',
         'is_sync'
    ];
}
