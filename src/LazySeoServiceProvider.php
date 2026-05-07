<?php

namespace Step2dev\LazySeoTools;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schedule;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Step2dev\LazySeoTools\Commands\ContentIntelligenceCommand;
use Step2dev\LazySeoTools\Commands\CrawlSiteCommand;
use Step2dev\LazySeoTools\Commands\ExportRedirectsCommand;
use Step2dev\LazySeoTools\Commands\GenerateSitemapCommand;
use Step2dev\LazySeoTools\Commands\ImportRedirectsCommand;
use Step2dev\LazySeoTools\Commands\IndexNowCommand;
use Step2dev\LazySeoTools\Commands\LazySeoCommand;
use Step2dev\LazySeoTools\Commands\MonitorSeoCommand;
use Step2dev\LazySeoTools\Commands\QueueSeoScanCommand;
use Step2dev\LazySeoTools\Commands\SeoHistoryCommand;
use Step2dev\LazySeoTools\Commands\WarmSitemapCommand;
use Step2dev\LazySeoTools\Contracts\SeoResolver;
use Step2dev\LazySeoTools\Http\Livewire\RedirectTable;
use Step2dev\LazySeoTools\Http\Livewire\SeoAnalyzerLivewire;
use Step2dev\LazySeoTools\Http\Livewire\SeoForm;
use Step2dev\LazySeoTools\Http\Livewire\SeoIssuesTable;
use Step2dev\LazySeoTools\Http\Livewire\SeoMonitoringDashboard;
use Step2dev\LazySeoTools\Http\Livewire\SeoScanDetail;
use Step2dev\LazySeoTools\Services\CanonicalService;
use Step2dev\LazySeoTools\Services\ContentIntelligenceService;
use Step2dev\LazySeoTools\Services\IndexNowService;
use Step2dev\LazySeoTools\Services\JsonLdService;
use Step2dev\LazySeoTools\Services\OGImageService;
use Step2dev\LazySeoTools\Services\OgMetaService;
use Step2dev\LazySeoTools\Services\RedirectImportExportService;
use Step2dev\LazySeoTools\Services\SchemaService;
use Step2dev\LazySeoTools\Services\SeoAlertService;
use Step2dev\LazySeoTools\Services\SeoAnalyzerService;
use Step2dev\LazySeoTools\Services\SeoAuditService;
use Step2dev\LazySeoTools\Services\SeoDashboardService;
use Step2dev\LazySeoTools\Services\SeoHistoryService;
use Step2dev\LazySeoTools\Services\SeoManager;
use Step2dev\LazySeoTools\Services\SeoMonitoringService;
use Step2dev\LazySeoTools\Services\SeoScanReportService;
use Step2dev\LazySeoTools\Services\SiteCrawlerService;
use Step2dev\LazySeoTools\Services\SitemapGeneratorService;
use Step2dev\LazySeoTools\Services\UrlNormalizer;
use Step2dev\LazySeoTools\View\Components\JsonLdComponent;
use Step2dev\LazySeoTools\View\Components\MetaComponent;
use Step2dev\LazySeoTools\View\Components\OgComponent;
use Step2dev\LazySeoTools\View\Components\TitleComponent;
use Step2dev\LazySeoTools\View\Components\TwitterComponent;

class LazySeoServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('lazy-seo')
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasMigrations([
                '2025_06_19_000001_create_seo_table',
                '2025_06_19_000002_create_seo_redirects_table',
                '2025_06_19_000003_create_seo_templates_table',
                '2025_06_19_000004_create_seo_scans_table',
                '2025_06_19_000005_create_seo_scan_issues_table',
                '2025_06_19_000006_create_seo_indexing_logs_table',
                '2025_06_19_000007_add_workflow_columns_to_seo_scan_issues_table',
                '2025_06_19_000008_add_status_columns_to_seo_scans_table',
            ])
            ->hasRoute('web')
            ->hasRoute('api')
            ->hasCommands([
                LazySeoCommand::class,
                GenerateSitemapCommand::class,
                WarmSitemapCommand::class,
                ImportRedirectsCommand::class,
                ExportRedirectsCommand::class,
                CrawlSiteCommand::class,
                MonitorSeoCommand::class,
                IndexNowCommand::class,
                ContentIntelligenceCommand::class,
                QueueSeoScanCommand::class,
                SeoHistoryCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->mergeLazySeoDefaults();

        $this->app->scoped(SeoManager::class);
        $this->app->alias(SeoManager::class, SeoResolver::class);
        $this->app->alias(SeoManager::class, 'lazy-seo');
        $this->app->alias(SeoManager::class, 'lazy-seo-manager');

        $this->app->singleton(SitemapGeneratorService::class);
        $this->app->singleton(UrlNormalizer::class);
        $this->app->singleton(SiteCrawlerService::class);
        $this->app->singleton(RedirectImportExportService::class);
        $this->app->singleton(SeoAnalyzerService::class);
        $this->app->singleton(SeoAuditService::class);
        $this->app->singleton(SeoScanReportService::class);
        $this->app->singleton(SeoAlertService::class);
        $this->app->singleton(SeoDashboardService::class);
        $this->app->singleton(SeoMonitoringService::class);
        $this->app->singleton(IndexNowService::class);
        $this->app->singleton(ContentIntelligenceService::class);
        $this->app->singleton(SeoHistoryService::class);
        $this->app->singleton(SchemaService::class);
        $this->app->singleton(CanonicalService::class);
        $this->app->singleton(JsonLdService::class);
        $this->app->singleton(OgMetaService::class);
        $this->app->singleton(OGImageService::class);
    }

    public function packageBooted(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'seo');

        if ($this->featureEnabled('meta')) {
            Blade::component('lazy-seo-meta', MetaComponent::class);
            Blade::component('lazy-seo-title', TitleComponent::class);
            Blade::component('lazy-seo-og', OgComponent::class);
            Blade::component('lazy-seo-twitter', TwitterComponent::class);
        }

        if ($this->featureEnabled('schema')) {
            Blade::component('lazy-seo-jsonld', JsonLdComponent::class);
            Blade::component('lazy-seo-schema', JsonLdComponent::class);
            Blade::component('seo::json-ld', JsonLdComponent::class);
            Blade::component('seo::schema', JsonLdComponent::class);
        }

        if ($this->featureEnabled('monitoring') && (bool) config('lazy-seo.monitoring.enabled', true) && config('lazy-seo.monitoring.schedule')) {
            $this->app->booted(function (): void {
                $command = (bool) config('lazy-seo.monitoring.scheduled_queue', false)
                    ? 'lazy-seo:monitor --queue'
                    : 'lazy-seo:monitor';

                Schedule::command($command)
                    ->cron((string) config('lazy-seo.monitoring.schedule'))
                    ->withoutOverlapping();
            });
        }

        if ($this->featureEnabled('livewire') && class_exists(Livewire::class)) {
            $this->registerLivewireComponents();
        }
    }

    protected function registerLivewireComponents(): void
    {
        try {
            Livewire::component('lazy-seo-form', SeoForm::class);
            Livewire::component('lazy-seo-analyzer', SeoAnalyzerLivewire::class);
            Livewire::component('lazy-seo-redirect-table', RedirectTable::class);
            Livewire::component('lazy-seo-monitoring-dashboard', SeoMonitoringDashboard::class);
            Livewire::component('lazy-seo-issues-table', SeoIssuesTable::class);
            Livewire::component('lazy-seo-scan-detail', SeoScanDetail::class);
        } catch (BindingResolutionException) {
            return;
        }
    }

    protected function featureEnabled(string $feature): bool
    {
        return (bool) config("lazy-seo.features.{$feature}", true);
    }

    protected function mergeLazySeoDefaults(): void
    {
        /** @var array<string, mixed> $defaults */
        $defaults = require __DIR__.'/../config/lazy-seo-defaults.php';

        /** @var array<string, mixed> $published */
        $published = config('lazy-seo', []);

        config()->set('lazy-seo', $this->mergeMissingConfig($published, $defaults));
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $defaults
     * @return array<string, mixed>
     */
    protected function mergeMissingConfig(array $config, array $defaults): array
    {
        foreach ($defaults as $key => $value) {
            if (! array_key_exists($key, $config)) {
                $config[$key] = $value;

                continue;
            }

            if (is_array($config[$key]) && is_array($value)) {
                $config[$key] = $this->mergeMissingConfig($config[$key], $value);
            }
        }

        return $config;
    }
}
