<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyCategory extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'icon',
        'image',
        'meta_title',
        'meta_description',
        'sort_order',
        'is_active',
    ];

    public function parent()
    {
        return $this->belongsTo(
            PropertyCategory::class,
            'parent_id'
        );
    }

    public function children()
    {
        return $this->hasMany(
            PropertyCategory::class,
            'parent_id'
        )->orderBy('sort_order');
    }

    public function properties()
    {
        return $this->hasMany(
            Property::class,
            'property_category_id'
        );
    }

    public function menus()
    {
        return $this->hasMany(
            PropertyCategoryMenu::class,
            'property_category_id'
        );
    }
}
