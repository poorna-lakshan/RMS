<?php

namespace App\Models\default;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Codegen extends Model
{
    use HasFactory;

    protected $table = 'default_codegen';

    protected $fillable = [
        'type',
        'prefix',
        'pad',
        'no',

    ]
}
