<?php

namespace Step2dev\LazySeoTools\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Step2dev\LazySeoTools\Models\Seo;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_seo_entry(): void
    {
        $seo = Seo::create([
            'title' => 'Test',
            'description' => 'Test desc',
            'keywords' => 'one, two, three',
            'seoable_type' => 'App\\Models\\Page',
            'seoable_id' => 1,
        ]);

        expect($seo->getTranslation('title', 'en'))->toBe('Test');

        $this->assertDatabaseHas('seo', ['title' => json_encode(['en' => 'Test'])]);
    }
}
