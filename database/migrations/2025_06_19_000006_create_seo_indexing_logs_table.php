<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('lazy-seo.tables.seo_indexing_logs', 'seo_indexing_logs'), function (Blueprint $table): void {
            $table->id();
            $table->string('engine')->default('indexnow');
            $table->string('host')->nullable();
            $table->json('urls');
            $table->unsignedSmallInteger('status')->nullable();
            $table->boolean('successful')->default(false);
            $table->text('response_body')->nullable();
            $table->json('payload')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['engine', 'successful']);
            $table->index('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('lazy-seo.tables.seo_indexing_logs', 'seo_indexing_logs'));
    }
};
