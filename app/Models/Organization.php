<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $fillable = [
        'name',
        'street',
        'house_number',
        'postal_code',
        'city',
        'oib',
        'iban',
        'email',
        'responsible_person_first_name',
        'responsible_person_last_name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
