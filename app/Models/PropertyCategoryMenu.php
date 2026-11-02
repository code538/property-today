<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyCategoryMenu extends Model
{
    protected $fillable = [
        'property_category_id',
        'menu_type',
        'menu_label',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(
            PropertyCategory::class,
            'property_category_id'
        );
    }
}
