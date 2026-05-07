<?php

namespace Step2dev\LazySeoTools\Services;

use Illuminate\Support\Facades\File;
use Step2dev\LazySeoTools\Models\SeoRedirect;

class RedirectImportExportService
{
    /**
     * @return array{created: int, updated: int, skipped: int}
     */
    public function importCsv(string $path, bool $updateExisting = true): array
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new \InvalidArgumentException("CSV file is not readable: {$path}");
        }

        $handle = fopen($path, 'rb');
        $header = null;
        $created = 0;
        $updated = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if ($row === [null] || $row === false) {
                continue;
            }

            if ($header === null) {
                $header = array_map(fn (string $value): string => trim($value), $row);

                continue;
            }

            $data = array_combine($header, array_pad($row, count($header), null));

            if (! is_array($data) || empty($data['old_url'])) {
                $skipped++;

                continue;
            }

            $payload = [
                'old_url' => trim((string) $data['old_url']),
                'new_url' => isset($data['new_url']) && $data['new_url'] !== '' ? trim((string) $data['new_url']) : null,
                'status_code' => (int) ($data['status_code'] ?? 301),
                'enabled' => filter_var($data['enabled'] ?? true, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true,
                'is_regex' => filter_var($data['is_regex'] ?? false, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false,
            ];

            if (! in_array($payload['status_code'], config('lazy-seo.redirects.allowed_status_codes', [301, 302, 307, 308, 410]), true)) {
                $skipped++;

                continue;
            }

            $existing = SeoRedirect::query()->where('old_url', $payload['old_url'])->first();

            if ($existing && $updateExisting) {
                $existing->update($payload);
                $updated++;

                continue;
            }

            if ($existing) {
                $skipped++;

                continue;
            }

            SeoRedirect::create($payload);
            $created++;
        }

        fclose($handle);

        return compact('created', 'updated', 'skipped');
    }

    public function exportCsv(string $path): string
    {
        File::ensureDirectoryExists(dirname($path));

        $handle = fopen($path, 'wb');
        fputcsv($handle, ['old_url', 'new_url', 'status_code', 'enabled', 'is_regex', 'hits', 'last_hit_at']);

        SeoRedirect::query()
            ->orderBy('id')
            ->chunk(500, function ($redirects) use ($handle): void {
                foreach ($redirects as $redirect) {
                    fputcsv($handle, [
                        $redirect->old_url,
                        $redirect->new_url,
                        $redirect->status_code,
                        $redirect->enabled ? 1 : 0,
                        $redirect->is_regex ? 1 : 0,
                        $redirect->hits,
                        optional($redirect->last_hit_at)->toDateTimeString(),
                    ]);
                }
            });

        fclose($handle);

        return $path;
    }
}
