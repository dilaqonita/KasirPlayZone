<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Checkin extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'checkins';

    protected $fillable = [

        'transaction_id',
        'kode_qr',
        'customer_name',
        'nama_paket',
        'waktu_masuk'

    ];

    public $timestamps = true;
}