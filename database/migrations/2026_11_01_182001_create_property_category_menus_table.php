<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_category_menus', function (Blueprint $table) {

            $table->id();

            // Reference to property category
            $table->foreignId('property_category_id')
                ->constrained('property_categories')
                ->cascadeOnDelete();

            // buy, rent, land
            $table->enum('menu_type', [
                'buy',
                'rent',
                'land'
            ]);

            // Custom dropdown label
            $table->string('menu_label', 150)->nullable();

            // Menu ordering
            $table->unsignedInteger('sort_order')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Prevent duplicate category in same menu
            $table->unique(
                ['menu_type', 'property_category_id'],
                'property_category_menu_unique'
            );

            $table->index([
                'menu_type',
                'is_active',
                'sort_order'
            ]);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_category_menus');
    }
};
