<?php

use Step2dev\LazySeoTools\Contracts\AIProvider;
use Step2dev\LazySeoTools\Services\AISeoService;
use Step2dev\LazySeoTools\Services\CTRPredictorService;

class FakeLazySeoAIProvider implements AIProvider
{
    public function __construct(private array|null $response) {}

    public function chatJson(array $messages): ?array
    {
        return $this->response;
    }
}

it('keeps ai services silent when ai is disabled', function (): void {
    config()->set('lazy-seo.ai.enabled', false);

    $seo = new AISeoService(new FakeLazySeoAIProvider(['title' => 'Ignored']));
    $ctr = new CTRPredictorService(new FakeLazySeoAIProvider(['ctr_percent' => 80]));

    expect($seo->generateMeta('content'))
        ->toBe(['title' => '', 'description' => '', 'keywords' => ''])
        ->and($ctr->predict('title', 'description'))
        ->toBe(['raw' => '', 'ctr' => 'N/A', 'score' => null]);
});

it('uses configured ai provider through an abstraction', function (): void {
    config()->set('lazy-seo.ai.enabled', true);

    $seo = new AISeoService(new FakeLazySeoAIProvider([
        'title' => 'Clean title',
        'description' => 'Clean description',
        'keywords' => 'laravel, seo',
    ]));

    expect($seo->generateMeta('content'))
        ->toBe([
            'title' => 'Clean title',
            'description' => 'Clean description',
            'keywords' => 'laravel, seo',
        ]);
});
