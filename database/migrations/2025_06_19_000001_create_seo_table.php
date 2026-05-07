<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('lazy-seo.tables.seo', 'seo'), function (Blueprint $table) {
            $table->id();
            $table->string('url', 2048)->nullable();
            $table->char('url_hash', 40)->nullable()->index();
            $table->json('title')->nullable();
            $table->json('description')->nullable();
            $table->json('keywords')->nullable();
            $table->string('canonical_url', 2048)->nullable();
            $table->json('robots')->nullable();
            $table->boolean('indexable')->default(true)->index();
            $table->nullableMorphs('seoable');
            $table->timestamps();

            $table->unique(['seoable_type', 'seoable_id'], 'seo_unique_seoable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('lazy-seo.tables.seo', 'seo'));
    }
};
