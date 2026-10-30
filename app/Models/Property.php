<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Property extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'listing_code',
        'slug',
        'user_id',
        'property_type_id',
        'title',
        'description',
        'listing_type',
        'price',
        'price_negotiable',
        'maintenance_charge',
        'security_deposit',
        'price_period',
        'built_up_area',
        'carpet_area',
        'plot_area',
        'area_unit',
        'bedrooms',
        'bathrooms',
        'balconies',
        'floor_number',
        'total_floors',
        'furnishing_status',
        'property_condition',
        'facing_direction',
        'year_built',
        'possession_status',
        'total_rooms',
        'available_rooms',
        'total_beds',
        'available_beds',
        'tenant_preference',
        'address_line_1',
        'address_line_2',
        'locality',
        'landmark',
        'city',
        'district',
        'state',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'contact_name',
        'contact_phone',
        'show_owner_name',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'published_at',
        'expires_at',
        'is_featured',
        'featured_until',
        'view_count',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'maintenance_charge' => 'decimal:2',
            'security_deposit' => 'decimal:2',
            'built_up_area' => 'decimal:2',
            'carpet_area' => 'decimal:2',
            'plot_area' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'price_negotiable' => 'boolean',
            'show_owner_name' => 'boolean',
            'is_featured' => 'boolean',
            'approved_at' => 'datetime',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'featured_until' => 'datetime',
            'view_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function propertyType(): BelongsTo
    {
        return $this->belongsTo(PropertyType::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class)
            ->orderBy('sort_order');
    }

    public function primaryImage(): HasMany
    {
        return $this->hasMany(PropertyImage::class)
            ->where('is_primary', true);
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(
            Amenity::class,
            'amenity_property'
        )->withPivot('details')->withTimestamps();
    }
}
