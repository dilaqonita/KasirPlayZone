<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Notification extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'notifications';

    public $timestamps = false;

    protected $fillable = [
        'type',
        'title',
        'sub',
        'time_ts',
        'is_read',
    ];
}