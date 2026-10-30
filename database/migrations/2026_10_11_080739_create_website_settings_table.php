
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_settings', function (Blueprint $table) {
            $table->id();

            // Company information
            $table->string('company_name');
            $table->string('company_email')->nullable();
            $table->string('company_phone', 30)->nullable();
            $table->text('company_address')->nullable();

            // Website information
            $table->string('website_name')->nullable();
            $table->string('website_url')->nullable();
            $table->string('tagline')->nullable();

            // Branding images
            $table->string('logo_web_path')->nullable();
            $table->string('logo_mobile_path')->nullable();
            $table->string('logo_alt')->nullable();
            $table->string('favicon_path')->nullable();

            // Default SEO metadata
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();

            // Social sharing images
            $table->string('og_image_web_path')->nullable();
            $table->string('og_image_mobile_path')->nullable();
            $table->string('og_image_alt')->nullable();

            // Social links
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('youtube_url')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_settings');
    }
};
