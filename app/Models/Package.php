<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Package extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'packages';

    protected $fillable = [
        'name',
        'emoji',
        'description',
        'age_label',
        'min_age',
        'max_age',
        'duration_minutes',
        'duration_hours',
        'price',
        'features',
        'is_active',
        'status',
    ];

    protected $casts = [
        'features'         => 'array',
        'price'            => 'integer',
        'duration_minutes' => 'integer',
        'duration_hours'   => 'float',
        'is_active'        => 'boolean',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaksi::class, 'package_id');
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.') . '/org';
    }
}