<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expences extends Model
{
    use HasFactory;

    protected $table = 'expence_category';

    protected $fillable = [
        'ware_house_id',
        'category_id',
        'description',
        'amount',
        'user'
    ];
}
