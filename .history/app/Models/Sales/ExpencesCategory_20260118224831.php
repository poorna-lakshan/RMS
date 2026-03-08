<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpencesCategory extends Model
{
    use HasFactory;
    protected $table = 'expence_category';

    protected $fillable = [
        'description'
    ];
}
