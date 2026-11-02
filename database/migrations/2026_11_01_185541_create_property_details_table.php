
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_details', function (Blueprint $table) {

            $table->id();

            // Property relationship
            $table->foreignId('property_id')
                ->unique()
                ->constrained('properties')
                ->cascadeOnDelete();


            // ---------------------------------
            // Plot / Land Information
            // ---------------------------------

            $table->decimal('plot_length', 12, 2)->nullable();

            $table->decimal('plot_width', 12, 2)->nullable();

            $table->string('facing', 40)->nullable();

            $table->decimal('road_width', 10, 2)->nullable();


            // ---------------------------------
            // PG / Hostel Information
            // ---------------------------------

            $table->string('sharing_type', 60)->nullable();

            $table->unsignedSmallInteger('total_beds')->nullable();

            $table->unsignedSmallInteger('available_beds')->nullable();

            $table->boolean('food_available')->nullable();


            // ---------------------------------
            // Commercial Information
            // ---------------------------------

            $table->string('office_type', 80)->nullable();

            $table->unsignedSmallInteger('number_of_cabins')->nullable();

            $table->unsignedSmallInteger('number_of_washrooms')->nullable();


            // ---------------------------------
            // Additional Dynamic Information
            // ---------------------------------

            $table->json('attributes')->nullable();


            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_details');
    }
};
