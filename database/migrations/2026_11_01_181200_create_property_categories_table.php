
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_categories', function (Blueprint $table) {

            $table->id();

            // Parent category
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('property_categories')
                ->nullOnDelete();

            // Category information
            $table->string('name', 150);
            $table->string('slug', 180)->unique();

            $table->text('description')->nullable();

            // Category icon and image
            $table->string('icon')->nullable();
            $table->string('image')->nullable();

            // SEO information
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // Display ordering
            $table->unsignedInteger('sort_order')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['parent_id', 'is_active']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_categories');
    }
};
