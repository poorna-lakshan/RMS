<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kitchen extends Model
{
    use HasFactory;

    protected $table = 'kitchen';

    protected $fillable = [
        'code',
        'name',
        'printer_name',
        'is_sync'

    ];

     protected $casts = [
        'is_sync' => 'boolean',
    ];
    public function items()
    {
        return $this->belongsToMany(
            Item::class,
            'item_kitchen',
            'kitchen_id',
            'item_id'
        )
            ->withTimestamps();
    }
}
