<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $remaining
 */
class DamageReport extends Model
{
     protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity',
        'expiry_date',
        'status',
        'resolved_quantity',
        'note',
        'remaining'
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

     public function getRemainingAttribute()
    {
        return max(0, $this->quantity - $this->resolved_quantity);
    }
}

