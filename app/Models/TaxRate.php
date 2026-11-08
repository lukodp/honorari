<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    protected $fillable = [
        'city',
        'local_government_id',
        'iban',
        'rate',
        'coefficient',
        'valid_from',
        'valid_until',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'coefficient' => 'decimal:6',
            'valid_from' => 'date',
            'valid_until' => 'date',
        ];
    }
    public static function findApplicable(
    int $localGovernmentId,
    string $paymentDate
): ?self {
    return self::query()
        ->where('local_government_id', $localGovernmentId)
        ->whereDate('valid_from', '<=', $paymentDate)
        ->where(function ($query) use ($paymentDate) {
            $query->whereNull('valid_until')
                ->orWhereDate('valid_until', '>=', $paymentDate);
        })
        ->orderByDesc('valid_from')
        ->first();
}
    public function localGovernment()
    {
    return $this->belongsTo(LocalGovernment::class);
    }
}
