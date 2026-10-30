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

            // Listing identification
            $table->string('listing_code', 50)->unique();
            $table->string('slug', 255)->unique();

            // Ownership and category
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('property_type_id')
                ->constrained('property_types')
                ->restrictOnDelete();

            // Basic information
            $table->string('title', 255);
            $table->longText('description')->nullable();

            // Sale, rent or lease
            $table->enum('listing_type', [
                'sale',
                'rent',
                'lease',
            ]);

            $table->decimal('price', 15, 2);
            $table->boolean('price_negotiable')->default(false);
            $table->decimal('maintenance_charge', 12, 2)
                ->nullable();
            $table->decimal('security_deposit', 15, 2)
                ->nullable();
            $table->string('price_period', 30)->nullable();

            // Property dimensions
            $table->decimal('built_up_area', 12, 2)->nullable();
            $table->decimal('carpet_area', 12, 2)->nullable();
            $table->decimal('plot_area', 12, 2)->nullable();
            $table->string('area_unit', 30)->default('sq_ft');

            // Room and building details
            $table->unsignedSmallInteger('bedrooms')->nullable();
            $table->unsignedSmallInteger('bathrooms')->nullable();
            $table->unsignedSmallInteger('balconies')->nullable();
            $table->unsignedSmallInteger('floor_number')->nullable();
            $table->unsignedSmallInteger('total_floors')->nullable();

            $table->string('furnishing_status', 30)->nullable();
            $table->string('property_condition', 50)->nullable();
            $table->string('facing_direction', 30)->nullable();
            $table->unsignedSmallInteger('year_built')->nullable();
            $table->string('possession_status', 50)->nullable();

            // Hostel and PG details
            $table->unsignedSmallInteger('total_rooms')->nullable();
            $table->unsignedSmallInteger('available_rooms')->nullable();
            $table->unsignedSmallInteger('total_beds')->nullable();
            $table->unsignedSmallInteger('available_beds')->nullable();
            $table->enum('tenant_preference', [
                'any',
                'male',
                'female',
                'family',
            ])->nullable();

            // Address and location
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('locality')->nullable();
            $table->string('landmark')->nullable();
            $table->string('city', 100);
            $table->string('district', 100)->nullable();
            $table->string('state', 100);
            $table->string('country', 100)->default('India');
            $table->string('postal_code', 20)->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Contact person for this listing (optional)
            $table->string('contact_name', 100)->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->boolean('show_owner_name')->default(true);

            // Moderation and publication
            $table->enum('status', [
                'draft',
                'pending',
                'published',
                'rejected',
                'sold',
                'rented',
                'archived',
            ])->default('pending');

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // Featured listings and statistics
            $table->boolean('is_featured')->default(false);
            $table->timestamp('featured_until')->nullable();
            $table->unsignedBigInteger('view_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Search indexes
            $table->index(['user_id', 'status']);
            $table->index(['property_type_id', 'status']);
            $table->index(['listing_type', 'status']);
            $table->index(['city', 'state']);
            $table->index(['price', 'listing_type']);
            $table->index(['latitude', 'longitude']);
            $table->index(['is_featured', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
