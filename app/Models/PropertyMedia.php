<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyMedia extends Model
{
    protected $fillable = [
        'property_id',
        'media_type',
        'file_path',
        'mobile_file_path',
        'image_alt',
        'mime_type',
        'file_size',
        'is_primary',
        'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'file_size' => 'integer',
        'sort_order' => 'integer',
    ];

    public function property()
    {
        return $this->belongsTo(
            Property::class,
            'property_id'
        );
    }
}
