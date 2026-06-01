<?php

namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use App\Models\User;
use App\Models\Transaksi;

class ActivityLog extends Model
{

protected $connection = 'mongodb';

protected $collection = 'activity_logs';

protected $fillable = [

'transaction_id',
'customer_name',
'type',
'package_name',
'performed_by'

];

protected $casts = [

'created_at' => 'datetime',
'updated_at' => 'datetime',

];

public function transaction(): BelongsTo
{

return $this->belongsTo(
Transaksi::class,
'transaction_id'
);

}

public function performer(): BelongsTo
{

return $this->belongsTo(
User::class,
'performed_by'
);

}

}