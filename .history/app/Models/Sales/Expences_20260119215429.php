<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expences extends Model
{
    use HasFactory;

    protected $table = 'expences'; // Changed from 'expence_category'

    protected $fillable = [
        'code',
        'ware_house_id',
        'category_id',
        'description',
        'amount',
        'user',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the category that owns the expense.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpencesCategory::class, 'category_id');
    }

    
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Inventory\Warehouse::class, 'ware_house_id');
    }

  
   
}