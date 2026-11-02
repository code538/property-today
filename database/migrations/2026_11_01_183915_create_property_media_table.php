
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_media', function (Blueprint $table) {

            $table->id();

            // Property relationship
            $table->foreignId('property_id')
                ->constrained('properties')
                ->cascadeOnDelete();

            // Image or video
            $table->enum('media_type', [
                'image',
                'video'
            ])->default('image');

            // Desktop image or video path
            $table->string('file_path', 500);

            // Separate mobile image path
            $table->string('mobile_file_path', 500)->nullable();

            // Image SEO alt text
            $table->string('image_alt', 255)->nullable();

            // File information
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            // Primary image
            $table->boolean('is_primary')->default(false);

            // Image ordering
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            // Indexes
            $table->index([
                'property_id',
                'media_type'
            ]);

            $table->index([
                'property_id',
                'is_primary'
            ]);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_media');
    }
};
