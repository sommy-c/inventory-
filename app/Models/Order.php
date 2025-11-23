<?php

// app/Models/Order.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'supplier_name',
        'order_number',
        'expected_date',
        'reference',
        'notes',
        'status',
        'total',
        'manager_name',
        'manager_signed_at',
        'admin_name',
        'admin_approved_at',
        'created_by',
    ];

    protected $casts = [
        'expected_date'      => 'date',
        'manager_signed_at'  => 'datetime',
        'admin_approved_at'  => 'datetime',
        'total'              => 'decimal:2',
    ];

    // Relationships
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Helpers
    public function isWaiting(): bool
    {
        return $this->status === 'waiting';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSupplied(): bool
    {
        return $this->status === 'supplied';
    }
}
