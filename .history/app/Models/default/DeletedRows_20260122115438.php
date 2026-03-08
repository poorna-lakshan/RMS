<?php

namespace App\Models\default;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeletedRows extends Model
{
    use HasFactory;
     protected $table = 'deleted_rows';

    protected $fillable = [
        'type',
        'key',
        'is_sync',
    ];
}
