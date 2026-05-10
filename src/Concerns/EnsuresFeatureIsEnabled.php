<?php

namespace Step2dev\LazySeoTools\Concerns;

trait EnsuresFeatureIsEnabled
{
    protected function ensureFeatureIsEnabled(string $feature, ?string $message = null): bool
    {
        if ((bool) config("lazy-seo.features.{$feature}", true)) {
            return true;
        }

        $this->components->error($message ?: "Lazy SEO feature [{$feature}] is disabled. Enable lazy-seo.features.{$feature} first.");

        return false;
    }
}
