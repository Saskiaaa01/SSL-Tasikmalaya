<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SslCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain_id',
        'status',
        'issuer',
        'valid_from',
        'valid_to',
        'days_remaining',
        'fingerprint_sha256',
        'chain_details',
        'error_message',
        'checked_at',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
        'checked_at' => 'datetime',
        'chain_details' => 'array',
        'days_remaining' => 'integer',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }
}

