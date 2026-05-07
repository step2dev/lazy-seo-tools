<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(config('lazy-seo.tables.seo_scan_issues', 'seo_scan_issues'), function (Blueprint $table): void {
            $table->string('status')->default('open')->after('severity');
            $table->timestamp('resolved_at')->nullable()->after('context');
            $table->timestamp('ignored_at')->nullable()->after('resolved_at');
            $table->string('note')->nullable()->after('ignored_at');

            $table->index(['status', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::table(config('lazy-seo.tables.seo_scan_issues', 'seo_scan_issues'), function (Blueprint $table): void {
            $table->dropIndex(['status', 'severity']);
            $table->dropColumn(['status', 'resolved_at', 'ignored_at', 'note']);
        });
    }
};
