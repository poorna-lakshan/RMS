<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemBatch extends Model
{
    use HasFactory;

    protected $table = 'item_batch';

    protected $fillable = [
        'item_id',
        'ware_house_id',
        'batch_no',
        'expiry_date',
        'qty',
        'unit_cost',
        'received_date',
        'reference_no',
        'reference_type',
        'supplier_id',
        'notes',
        'is_consumed'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'received_date' => 'date',
        'qty' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'is_consumed' => 'boolean'
    ];

    // Relationships
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'ware_house_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('qty', '>', 0)
                     ->where('is_consumed', false);
    }

    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                     ->where('expiry_date', '>=', now())
                     ->where('qty', '>', 0);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now())
                     ->where('qty', '>', 0);
    }

    public function scopeByFifo($query)
    {
        return $query->orderBy('received_date')
                     ->orderBy('id')
                     ->orderBy('expiry_date');
    }

    public function scopeForItem($query, $itemId, $warehouseId)
    {
        return $query->where('item_id', $itemId)
                     ->where('ware_house_id', $warehouseId);
    }
}