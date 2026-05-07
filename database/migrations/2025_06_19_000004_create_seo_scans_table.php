<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('lazy-seo.tables.seo_scans', 'seo_scans'), function (Blueprint $table): void {
            $table->id();
            $table->string('start_url');
            $table->unsignedBigInteger('previous_scan_id')->nullable();
            $table->unsignedSmallInteger('score')->default(0);
            $table->smallInteger('score_delta')->default(0);
            $table->unsignedInteger('pages_count')->default(0);
            $table->unsignedInteger('issues_count')->default(0);
            $table->unsignedInteger('new_issues_count')->default(0);
            $table->unsignedInteger('resolved_issues_count')->default(0);
            $table->unsignedInteger('broken_links_count')->default(0);
            $table->unsignedInteger('redirect_chains_count')->default(0);
            $table->unsignedInteger('duplicate_titles_count')->default(0);
            $table->unsignedInteger('duplicate_descriptions_count')->default(0);
            $table->unsignedInteger('canonical_conflicts_count')->default(0);
            $table->json('summary')->nullable();
            $table->json('regressions')->nullable();
            $table->json('resolved_issues')->nullable();
            $table->json('options')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['start_url', 'created_at']);
            $table->index('score');
            $table->index('previous_scan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('lazy-seo.tables.seo_scans', 'seo_scans'));
    }
};
