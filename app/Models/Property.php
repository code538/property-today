<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'property_category_id',
        'listing_type',
        'title',
        'slug',
        'description',
        'price',
        'price_negotiable',
        'price_period',
        'maintenance_charge',
        'security_deposit',
        'built_up_area',
        'carpet_area',
        'plot_area',
        'area_unit',
        'bedrooms',
        'bathrooms',
        'balconies',
        'furnishing_status',
        'floor_number',
        'total_floors',
        'property_age',
        'possession_status',
        'available_from',
        'status',
        'is_featured',
        'views_count',
        'meta_title',
        'meta_description',
        'published_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_negotiable' => 'boolean',
        'maintenance_charge' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'built_up_area' => 'decimal:2',
        'carpet_area' => 'decimal:2',
        'plot_area' => 'decimal:2',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'balconies' => 'integer',
        'is_featured' => 'boolean',
        'views_count' => 'integer',
        'available_from' => 'date',
        'published_at' => 'datetime',
    ];

    // Property owner
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Property category
    public function category()
    {
        return $this->belongsTo(
            PropertyCategory::class,
            'property_category_id'
        );
    }

    // Property images and videos
    public function media()
    {
        return $this->hasMany(
            PropertyMedia::class,
            'property_id'
        )->orderBy('sort_order');
    }

    // Property location
    public function location()
    {
        return $this->hasOne(
            PropertyLocation::class,
            'property_id'
        );
    }

    // Property amenities
    public function amenities()
    {
        return $this->belongsToMany(
            Amenity::class,
            'property_amenities',
            'property_id',
            'amenity_id'
        );
    }

    // Additional property details
    public function details()
    {
        return $this->hasOne(
            PropertyDetail::class,
            'property_id'
        );
    }

  
}
