<?php

// app/Models/Setting.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        $row = static::where('key', $key)->first();
        return $row ? $row->value : $default;
    }

    public static function set(string $key, $value)
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    // Convenience for VAT as percentage or fraction:
    public static function vatPercent(): float
    {
        return (float) static::get('vat_rate', 0);      // e.g. 7.5
    }

    public static function vatRate(): float
    {
        return static::vatPercent() / 100;              // e.g. 0.075
    }
}

