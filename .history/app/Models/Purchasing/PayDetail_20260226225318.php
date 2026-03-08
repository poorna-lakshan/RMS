<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayDetail extends Model
{
    use HasFactory;

     protected $table = 'sup_pay_detail';

    protected $fillable = [
        'pay_id',
        'grn_id',
        'total',
        'due',
        'paid',
    ];


    // Relations
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

   
      public function Header()
    {
        return $this->belongsTo(PayHeader::class, 'pay_id');
    }
     public function details()
    {
        return $this->belongsTo(GRNHeader::class, 'grn_id');
    }
}
