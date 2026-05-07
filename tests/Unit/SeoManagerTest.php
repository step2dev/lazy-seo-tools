<?php

use Step2dev\LazySeoTools\Services\SeoManager;

it('can reset fluent state', function (): void {
    $manager = app(SeoManager::class);

    $manager->title('Custom title');
    expect($manager->toArray()['title'])->toBe('Custom title');

    $manager->reset();
    expect($manager->toArray()['title'])->not->toBe('Custom title');
});
