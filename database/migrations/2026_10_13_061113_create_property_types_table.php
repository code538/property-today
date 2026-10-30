
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_types', function (Blueprint $table) {
            $table->id();

            // Category details
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();

            // Allow parent and child categories
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('property_types')
                ->nullOnDelete();

            // Display and frontend
            $table->string('icon')->nullable();
            $table->string('image')->nullable();
            $table->string('image_alt')->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            // Property category capabilities
            $table->boolean('supports_sale')->default(true);
            $table->boolean('supports_rent')->default(true);
            $table->boolean('supports_lease')->default(false);
            $table->boolean('supports_bedrooms')->default(false);
            $table->boolean('supports_bathrooms')->default(false);
            $table->boolean('supports_area')->default(true);
            $table->boolean('supports_plot_area')->default(false);
            $table->boolean('supports_furnishing')->default(false);
            $table->boolean('supports_parking')->default(false);

            // Configurable additional form fields
            $table->json('form_schema')->nullable();

            // SEO
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();

            // Publishing controls
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);

            // Audit timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['is_active', 'sort_order']);
            $table->index(['parent_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_types');
    }
};
