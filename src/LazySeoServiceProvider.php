<?php

namespace Step2dev\LazySeoTools;

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
use Step2dev\LazySeoTools\Services\SeoAnalyzerService;
use Step2dev\LazySeoTools\Services\SeoAuditService;
use Step2dev\LazySeoTools\Services\SeoDashboardService;
use Step2dev\LazySeoTools\Services\SeoHistoryService;
use Step2dev\LazySeoTools\Services\SeoManager;
use Step2dev\LazySeoTools\Services\SeoMonitoringService;
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
            ->discoversMigrations()
            ->hasRoute('web')
            ->hasRoute('api')
            ->hasCommands([
                LazySeoCommand::class,
                GenerateSitemapCommand::class,
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

        Blade::component('lazy-seo-meta', MetaComponent::class);
        Blade::component('lazy-seo-title', TitleComponent::class);
        Blade::component('lazy-seo-jsonld', JsonLdComponent::class);
        Blade::component('lazy-seo-schema', JsonLdComponent::class);
        Blade::component('seo::json-ld', JsonLdComponent::class);
        Blade::component('seo::schema', JsonLdComponent::class);
        Blade::component('lazy-seo-og', OgComponent::class);
        Blade::component('lazy-seo-twitter', TwitterComponent::class);

        if ((bool) config('lazy-seo.monitoring.enabled', true) && config('lazy-seo.monitoring.schedule')) {
            $this->app->booted(function (): void {
                Schedule::command('lazy-seo:monitor')
                    ->cron((string) config('lazy-seo.monitoring.schedule'))
                    ->withoutOverlapping();
            });
        }

        if (class_exists(Livewire::class)) {
            Livewire::component('lazy-seo-form', SeoForm::class);
            Livewire::component('lazy-seo-analyzer', SeoAnalyzerLivewire::class);
            Livewire::component('lazy-seo-redirect-table', RedirectTable::class);
            Livewire::component('lazy-seo-monitoring-dashboard', SeoMonitoringDashboard::class);
            Livewire::component('lazy-seo-issues-table', SeoIssuesTable::class);
            Livewire::component('lazy-seo-scan-detail', SeoScanDetail::class);
        }
    }
}
