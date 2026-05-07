<?php

namespace Step2dev\LazySeoTools\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class OGImageService
{
    public function generate(string $title, ?string $path = null): string
    {
        $manager = new ImageManager(new Driver);
        $width = (int) config('lazy-seo.og_image.width', 1200);
        $height = (int) config('lazy-seo.og_image.height', 630);
        $disk = config('lazy-seo.og_image.disk', 'public');
        $directory = trim(config('lazy-seo.og_image.directory', 'og'), '/');

        $image = $manager->create($width, $height)->fill('#f9fafb');

        // Keep this dependency-free. Consumers can override this service for custom fonts/templates.
        $image->text($title, 80, (int) ($height / 2), function ($font) {
            $font->size(48);
            $font->color('#111827');
            $font->align('left');
            $font->valign('middle');
        });

        $path ??= $directory.'/'.md5($title).'.png';

        Storage::disk($disk)->put($path, (string) $image->toPng());

        return Storage::disk($disk)->url($path);
    }
}
