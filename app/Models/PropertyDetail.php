<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyDetail extends Model
{
    protected $fillable = [
        'property_id',
        'plot_length',
        'plot_width',
        'facing',
        'road_width',
        'sharing_type',
        'total_beds',
        'available_beds',
        'food_available',
        'office_type',
        'number_of_cabins',
        'number_of_washrooms',
        'attributes',
    ];

    protected $casts = [
        'plot_length' => 'decimal:2',
        'plot_width' => 'decimal:2',
        'road_width' => 'decimal:2',
        'food_available' => 'boolean',
        'attributes' => 'array',
    ];

    public function property()
    {
        return $this->belongsTo(
            Property::class,
            'property_id'
        );
    }
}
