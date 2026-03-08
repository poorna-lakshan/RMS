<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvPaymentMethod extends Model
{
    protected $table = 'inv_payment_method';

    protected $fillable = [
        'trans_id',
        'payment_method_id',
        'amount',
    ];

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function invoice()
    {
        return $this->belongsTo(InvoiceHeader::class, 'trans_id');
    }
}
