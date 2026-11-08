<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referee extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'oib',
        'address',
        'postal_code',
        'city',
        'local_government_id',
        'iban',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function localGovernment()
    {
    return $this->belongsTo(LocalGovernment::class, 'local_government_id');
    }
}
