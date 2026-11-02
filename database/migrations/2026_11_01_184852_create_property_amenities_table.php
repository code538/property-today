
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_amenities', function (Blueprint $table) {

            $table->id();

            // Property relationship
            $table->foreignId('property_id')
                ->constrained('properties')
                ->cascadeOnDelete();

            // Amenity relationship
            $table->foreignId('amenity_id')
                ->constrained('amenities')
                ->cascadeOnDelete();

            $table->timestamps();

            // Prevent duplicate amenity for same property
            $table->unique(
                ['property_id', 'amenity_id'],
                'property_amenity_unique'
            );

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_amenities');
    }
};
