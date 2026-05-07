<?php

it('uses env only inside config files', function (): void {
    $root = realpath(__DIR__.'/../..');
    $paths = ['src', 'routes', 'database', 'resources', 'tests'];
    $matches = [];

    foreach ($paths as $path) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$path));

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            if (str_contains($contents, 'env'.'(')) {
                $matches[] = str_replace($root.'/', '', $file->getPathname());
            }
        }
    }

    expect($matches)->toBe([]);
});
