<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Transaksi extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'transactions';

    protected $fillable = [
        'ticket_code',
        'customer_name',
        'customer_phone',
        'package_id',
        'visitor_count',
        'total_amount',
        'payment_method',
        'visit_date',
        'visit_time',
        'notes',
        'status',
        'check_in_at',
        'check_out_at',
        'duration_min',
        'slot_id',
        'status_pembayaran',
        'metode_pembayaran',
        'total_harga',
        'details',
        'ticket',
    ];

    protected $casts = [
        'details'       => 'array',
        'ticket'        => 'array',
        'check_in_at'   => 'datetime',
        'check_out_at'  => 'datetime',
        'visit_date'    => 'datetime',
        'total_amount'  => 'integer',
        'total_harga'   => 'integer',
        'visitor_count' => 'integer',
    ];

    // Paksa MongoDB pakai collection 'transactions' bukan 'transaksis'
    public function getTable(): string
    {
        return $this->collection;
    }

    public static function generateTicketCode(string $date = null): string
    {
        $prefix = 'TKT-' . ($date
            ? Carbon::parse($date)->format('ymd')
            : now()->format('ymd')
        ) . '-';

        do {
            $code = $prefix . strtoupper(Str::random(4));
        } while (static::where('ticket_code', $code)->exists());

        return $code;
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class, 'slot_id');
    }
}