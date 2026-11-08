<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Calculation extends Model
{
    protected $fillable = [
        'report_id',
        'organization_name',
        'organization_address',
        'organization_postal_code',
        'organization_city',
        'organization_oib',
        'organization_iban',
        'referee_id',
        'referee_first_name',
        'referee_last_name',
        'referee_oib',
        'referee_address',
        'referee_postal_code',
        'referee_city',
        'referee_iban',
        'referee_local_government_code',

        'payment_date',

        'net_amount',
        'gross_amount',
        'tax_rate',
        'coefficient',
        'tax_amount',
        'tax_iban',
        'tax_city',
        'tax_valid_from',
        'tax_valid_until',

        'status',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'tax_valid_from' => 'date',
            'tax_valid_until' => 'date',

            'net_amount' => 'decimal:2',
            'gross_amount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'coefficient' => 'decimal:6',
            'tax_amount' => 'decimal:2',
        ];
    }

    public function referee(): BelongsTo
    {
        return $this->belongsTo(Referee::class);
    }
}
