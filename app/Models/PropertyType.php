<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PropertyType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'description',
        'parent_id',
        'icon',
        'image',
        'image_alt',
        'sort_order',
        'supports_sale',
        'supports_rent',
        'supports_lease',
        'supports_bedrooms',
        'supports_bathrooms',
        'supports_area',
        'supports_plot_area',
        'supports_furnishing',
        'supports_parking',
        'form_schema',
        'meta_title',
        'meta_description',
        'is_active',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'supports_sale' => 'boolean',
            'supports_rent' => 'boolean',
            'supports_lease' => 'boolean',
            'supports_bedrooms' => 'boolean',
            'supports_bathrooms' => 'boolean',
            'supports_area' => 'boolean',
            'supports_plot_area' => 'boolean',
            'supports_furnishing' => 'boolean',
            'supports_parking' => 'boolean',
            'form_schema' => 'array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'parent_id'
        );
    }

    public function children(): HasMany
    {
        return $this->hasMany(
            self::class,
            'parent_id'
        )->orderBy('sort_order');
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }
}
