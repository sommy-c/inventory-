<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAlertRead extends Model
{
    protected $fillable = [
        'user_id',
        'alert_type',
        'product_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
