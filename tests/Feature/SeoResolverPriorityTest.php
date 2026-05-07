<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Step2dev\LazySeoTools\Concerns\HasSeo;
use Step2dev\LazySeoTools\Data\SeoData;
use Step2dev\LazySeoTools\Models\Seo;
use Step2dev\LazySeoTools\Services\SeoManager;

class LazySeoPriorityPost extends Model
{
    use HasSeo;

    protected $table = 'lazy_seo_priority_posts';

    protected $guarded = [];
}

it('keeps seo data immutable when merging overrides', function (): void {
    $data = SeoData::fromArray([
        'url' => '/first',
        'title' => 'First',
    ]);

    $changed = $data->with(['title' => 'Second']);

    expect($data->title)->toBe('First')
        ->and($changed->title)->toBe('Second');
});

it('resolves seo priority from defaults url model template and manual overrides', function (): void {
    app()->setLocale('en');

    Schema::create('lazy_seo_priority_posts', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    Seo::create([
        'url' => '/priority',
        'title' => ['en' => 'URL title'],
        'description' => ['en' => 'URL description'],
        'indexable' => true,
    ]);

    $post = LazySeoPriorityPost::query()->create(['name' => 'Post']);
    $post->updateSeo([
        'title' => ['en' => 'Model title'],
        'description' => ['en' => 'Model description'],
        'indexable' => true,
    ]);

    $data = app(SeoManager::class)
        ->reset()
        ->title('Manual title')
        ->resolve(model: $post, url: '/priority');

    expect($data->title)->toBe('Manual title')
        ->and($data->description)->toBe('Model description');
});
