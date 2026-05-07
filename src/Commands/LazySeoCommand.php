<?php

namespace Step2dev\LazySeoTools\Commands;

use Illuminate\Console\Command;

class LazySeoCommand extends Command
{
    public $signature = 'lazy-seo:about';

    public $description = 'Show Lazy SEO Tools package information.';

    public function handle(): int
    {
        $this->components->info('Lazy SEO Tools is installed.');

        return self::SUCCESS;
    }
}
