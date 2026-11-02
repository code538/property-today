<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyLocation extends Model
{
    protected $fillable = [
        'property_id',
        'country',
        'state',
        'city',
        'locality',
        'pincode',
        'address',
        'landmark',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function property()
    {
        return $this->belongsTo(
            Property::class,
            'property_id'
        );
    }
}
