
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amenities', function (Blueprint $table) {

            $table->id();

            // Amenity name
            $table->string('name', 120);

            // SEO-friendly unique slug
            $table->string('slug', 150)->unique();

            // Example: Security, Recreation, Basic Facilities
            $table->string('amenity_group', 80)->nullable();

            // Icon name or icon path
            $table->string('icon')->nullable();

            // Active / inactive
            $table->boolean('is_active')->default(true);

            // Display ordering
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['is_active', 'sort_order']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amenities');
    }
};
