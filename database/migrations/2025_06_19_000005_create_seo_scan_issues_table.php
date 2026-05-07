<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('lazy-seo.tables.seo_scan_issues', 'seo_scan_issues'), function (Blueprint $table): void {
            $table->id();
            $table->foreignId('seo_scan_id')->constrained(config('lazy-seo.tables.seo_scans', 'seo_scans'))->cascadeOnDelete();
            $table->string('url')->nullable();
            $table->string('type');
            $table->string('severity')->default('warning');
            $table->string('message');
            $table->char('fingerprint', 40)->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['type', 'severity']);
            $table->index('fingerprint');
            $table->index('url');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('lazy-seo.tables.seo_scan_issues', 'seo_scan_issues'));
    }
};
