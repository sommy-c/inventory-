<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku', 'barcode', 'name', 'description',
        'category_id', 'supplier_id',
        'purchase_price', 'selling_price',
        'quantity', 'reorder_level',
        'status', 'is_suspended',
        'expiry_date', 'supply_date'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'supply_date' => 'date',
        'is_suspended' => 'boolean',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    public function getStatusAttribute($value)
    {
        if ($this->is_suspended) return 'suspended';
        if ($this->quantity <= 0) return 'out_of_stock';
        return $value ?? 'active';
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
}
