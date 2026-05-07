<?php

namespace Step2dev\LazySeoTools\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Step2dev\LazySeoTools\Http\Middleware\HandleSeoRedirects;
use Step2dev\LazySeoTools\Models\SeoRedirect;
use Tests\TestCase;

class RedirectTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_handles_redirect_properly(): void
    {
        Route::middleware(HandleSeoRedirects::class)->get('/old-page', fn (): string => 'old page');

        SeoRedirect::create([
            'old_url' => 'old-page',
            'new_url' => 'new-page',
            'status_code' => 301,
            'enabled' => true,
        ]);

        $response = $this->get('/old-page');

        $response->assertRedirect('/new-page');
    }
}
