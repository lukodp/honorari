<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JoppdPreparation extends Model
{
    protected $fillable = [
        'organization_id',
        'organization_oib',
        'payment_date',
        'revision',
        'status',
        'fingerprint',
        'snapshot',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'snapshot' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function calculations(): BelongsToMany
    {
        return $this->belongsToMany(
            Calculation::class,
            'calculation_joppd_preparation'
        );
    }
}
