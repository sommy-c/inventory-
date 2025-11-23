<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'subtotal',
        'discount',
        'fee',
        'total',
        'amount_paid',
        'change',
        'payment_method',
        'status',
        'hold_number',
        'vat_amount',
        'vat_rate'
    ];

      public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_name', 'name'); // optional
    }
}
