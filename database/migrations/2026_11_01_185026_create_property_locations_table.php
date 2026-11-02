
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_locations', function (Blueprint $table) {

            $table->id();

            // One location per property
            $table->foreignId('property_id')
                ->unique()
                ->constrained('properties')
                ->cascadeOnDelete();

            // Country information
            $table->string('country', 100)->default('India');

            // State, city and locality
            $table->string('state', 120);
            $table->string('city', 120);
            $table->string('locality', 150)->nullable();

            // PIN code
            $table->string('pincode', 12)->nullable();

            // Full address
            $table->text('address')->nullable();

            // Nearby landmark
            $table->string('landmark', 180)->nullable();

            // Map coordinates
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->timestamps();

            // Search indexes
            $table->index(['state', 'city']);
            $table->index(['city', 'locality']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_locations');
    }
};
