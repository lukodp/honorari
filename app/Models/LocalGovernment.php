<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocalGovernment extends Model
{
    protected $fillable = [
        'code',
        'name',
    ];
public function taxRates()
{
    return $this->hasMany(TaxRate::class);
}
}