<?php

namespace App\Models\sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Inventory\Item;
class InvoiceDetail extends Model
{
    use HasFactory;

    protected $table = 'invoice_detail';
    protected $fillable = [
        'reference_no',
        'line_no',
        'item_id',
        'comment',
        'qty',
        'unit_cost',
        'unit_price',
    ];


    public function invoiceHeader()
    {
        return $this->belongsTo(InvoiceHeader::class, foreignKey: 'reference_no');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
