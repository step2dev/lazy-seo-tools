<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(config('lazy-seo.tables.seo_scans', 'seo_scans'), function (Blueprint $table): void {
            $table->string('status')->default('completed')->after('previous_scan_id');
            $table->string('failure_reason')->nullable()->after('options');
            $table->timestamp('started_at')->nullable()->after('failure_reason');

            $table->index(['status', 'created_at'], 'lazy_seo_scans_status_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table(config('lazy-seo.tables.seo_scans', 'seo_scans'), function (Blueprint $table): void {
            $table->dropIndex('lazy_seo_scans_status_created_at_index');
            $table->dropColumn(['status', 'failure_reason', 'started_at']);
        });
    }
};
