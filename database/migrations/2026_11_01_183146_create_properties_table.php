
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {

            $table->id();

            // Property owner (User / Agent / Admin)
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Property category
            $table->foreignId('property_category_id')
                ->constrained('property_categories')
                ->restrictOnDelete();

            // Sale, Rent or Lease
            $table->enum('listing_type', [
                'sale',
                'rent',
                'lease'
            ]);

            // Basic information
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();

            // Pricing information
            $table->decimal('price', 15, 2)->nullable();
            $table->boolean('price_negotiable')->default(false);

            $table->enum('price_period', [
                'daily',
                'weekly',
                'monthly',
                'yearly'
            ])->nullable();

            $table->decimal('maintenance_charge', 15, 2)
                ->nullable();

            $table->decimal('security_deposit', 15, 2)
                ->nullable();

            // Area information
            $table->decimal('built_up_area', 12, 2)->nullable();
            $table->decimal('carpet_area', 12, 2)->nullable();
            $table->decimal('plot_area', 12, 2)->nullable();

            $table->string('area_unit', 30)->default('sqft');

            // Residential information
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->unsignedTinyInteger('balconies')->nullable();

            $table->string('furnishing_status', 50)->nullable();

            // Floor information
            $table->unsignedSmallInteger('floor_number')->nullable();
            $table->unsignedSmallInteger('total_floors')->nullable();

            // Property age
            $table->unsignedSmallInteger('property_age')->nullable();

            // Possession information
            $table->string('possession_status', 50)->nullable();
            $table->date('available_from')->nullable();

            // Listing status
            $table->enum('status', [
                'draft',
                'pending',
                'published',
                'rejected',
                'inactive',
                'sold',
                'rented'
            ])->default('pending');

            // Featured property
            $table->boolean('is_featured')->default(false);

            // Property views
            $table->unsignedBigInteger('views_count')->default(0);

            // SEO information
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // Published date
            $table->timestamp('published_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Search indexes
            $table->index([
                'listing_type',
                'status',
                'property_category_id'
            ], 'properties_search_index');

            $table->index(['price', 'status']);

            $table->index(['is_featured', 'status']);

            $table->index(['created_at', 'status']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
