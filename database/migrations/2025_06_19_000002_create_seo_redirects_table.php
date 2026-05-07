<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('lazy-seo.tables.seo_redirects', 'seo_redirects'), function (Blueprint $table) {
            $table->id();
            $table->string('old_url', 2048);
            $table->string('normalized_old_url', 2048)->nullable();
            $table->char('normalized_old_url_hash', 40)->nullable();
            $table->string('new_url', 2048)->nullable();
            $table->unsignedSmallInteger('status_code')->default(301)->index();
            $table->boolean('enabled')->default(true)->index();
            $table->boolean('is_regex')->default(false)->index();
            $table->unsignedBigInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->timestamps();

            $table->index(['enabled', 'status_code', 'is_regex'], 'seo_redirect_lookup_flags');
            $table->index('normalized_old_url_hash', 'seo_redirect_normalized_hash_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('lazy-seo.tables.seo_redirects', 'seo_redirects'));
    }
};
