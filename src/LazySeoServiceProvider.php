<?php

namespace Step2dev\LazySeoTools;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schedule;
use Intervention\Image\ImageManager;
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
use Step2dev\LazySeoTools\Contracts\AIProvider;
use Step2dev\LazySeoTools\Contracts\SeoResolver;
use Step2dev\LazySeoTools\Http\Livewire\RedirectTable;
use Step2dev\LazySeoTools\Http\Livewire\SeoAnalyzerLivewire;
use Step2dev\LazySeoTools\Http\Livewire\SeoForm;
use Step2dev\LazySeoTools\Http\Livewire\SeoIssuesTable;
use Step2dev\LazySeoTools\Http\Livewire\SeoMonitoringDashboard;
use Step2dev\LazySeoTools\Http\Livewire\SeoScanDetail;
use Step2dev\LazySeoTools\Services\AI\OpenAIProvider;
use Step2dev\LazySeoTools\Services\AISeoService;
use Step2dev\LazySeoTools\Services\AISeoWriterService;
use Step2dev\LazySeoTools\Services\CanonicalService;
use Step2dev\LazySeoTools\Services\ContentIntelligenceService;
use Step2dev\LazySeoTools\Services\CTRPredictorService;
use Step2dev\LazySeoTools\Services\HtmlSeoParser;
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
use Step2dev\LazySeoTools\Support\LazySeoConfigValidator;
use Step2dev\LazySeoTools\View\Components\JsonLdComponent;
use Step2dev\LazySeoTools\View\Components\MetaComponent;
use Step2dev\LazySeoTools\View\Components\OgComponent;
use Step2dev\LazySeoTools\View\Components\TitleComponent;
use Step2dev\LazySeoTools\View\Components\TwitterComponent;

class LazySeoServiceProvider extends PackageServiceProvider
{
    /** @var array<int, class-string> */
    private const CORE_SERVICES = [
        UrlNormalizer::class,
        SeoAnalyzerService::class,
        SchemaService::class,
        CanonicalService::class,
        JsonLdService::class,
        OgMetaService::class,
    ];

    /** @var array<int, class-string> */
    private const SITEMAP_SERVICES = [
        SitemapGeneratorService::class,
    ];

    /** @var array<int, class-string> */
    private const CRAWLER_SERVICES = [
        HtmlSeoParser::class,
        SiteCrawlerService::class,
        SeoAuditService::class,
        SeoScanReportService::class,
    ];

    /** @var array<int, class-string> */
    private const MONITORING_SERVICES = [
        SeoAlertService::class,
        SeoDashboardService::class,
        SeoMonitoringService::class,
        SeoHistoryService::class,
    ];

    /** @var array<int, class-string> */
    private const AI_SERVICES = [
        AISeoService::class,
        AISeoWriterService::class,
        CTRPredictorService::class,
    ];

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

        $this->app->singleton(LazySeoConfigValidator::class);

        $this->registerCoreServices();
        $this->registerOptionalServices();
    }

    protected function registerCoreServices(): void
    {
        $this->registerSingletons(self::CORE_SERVICES);
        $this->app->singleton(AIProvider::class, OpenAIProvider::class);

        if ($this->featureEnabled('redirects')) {
            $this->app->singleton(RedirectImportExportService::class);
        }
    }

    protected function registerOptionalServices(): void
    {
        if ($this->featureEnabled('sitemap')) {
            $this->registerSingletons(self::SITEMAP_SERVICES);
        }

        if ($this->featureEnabled('crawler')) {
            $this->registerSingletons(self::CRAWLER_SERVICES);
        }

        if ($this->featureEnabled('monitoring')) {
            $this->registerSingletons(self::MONITORING_SERVICES);
        }

        if ($this->featureEnabled('indexnow')) {
            $this->app->singleton(IndexNowService::class);
        }

        if ($this->featureEnabled('content_intelligence')) {
            $this->app->singleton(ContentIntelligenceService::class);
        }

        if ($this->featureEnabled('og_image') && class_exists(ImageManager::class)) {
            $this->app->singleton(OGImageService::class);
        }

        if ((bool) config('lazy-seo.ai.enabled', false)) {
            $this->registerSingletons(self::AI_SERVICES);
        }
    }

    /**
     * @param  array<int, class-string>  $services
     */
    protected function registerSingletons(array $services): void
    {
        foreach ($services as $service) {
            $this->app->singleton($service);
        }
    }

    public function packageBooted(): void
    {
        $this->app->make(LazySeoConfigValidator::class)->validate();
        $this->registerAdminGate();

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
            $this->registerLivewireComponentsIfAvailable();
        }
    }

    protected function registerAdminGate(): void
    {
        if (! (bool) config('lazy-seo.routes.admin_gate_enabled', true)) {
            return;
        }

        $ability = (string) config('lazy-seo.routes.admin_gate', 'manage-lazy-seo');

        if ($ability === '' || Gate::has($ability)) {
            return;
        }

        Gate::define($ability, static function (mixed $user): bool {
            if (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo('manage seo')) {
                return true;
            }

            if (method_exists($user, 'can')) {
                return (bool) $user->can('manage seo');
            }

            return false;
        });
    }

    protected function registerLivewireComponentsIfAvailable(): void
    {
        foreach ($this->livewireComponents() as $name => $component) {
            Livewire::component($name, $component);
        }
    }

    /**
     * @return array<string, class-string>
     */
    protected function livewireComponents(): array
    {
        return [
            'lazy-seo-form' => SeoForm::class,
            'lazy-seo-analyzer' => SeoAnalyzerLivewire::class,
            'lazy-seo-redirect-table' => RedirectTable::class,
            'lazy-seo-monitoring-dashboard' => SeoMonitoringDashboard::class,
            'lazy-seo-issues-table' => SeoIssuesTable::class,
            'lazy-seo-scan-detail' => SeoScanDetail::class,
        ];
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
