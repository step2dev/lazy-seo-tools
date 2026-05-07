# Potentially extra or cleanup files

This file is based on the current package archive structure.

## Safe cleanup candidates

### `resources/views/.gitkeep`

Can be removed because `resources/views` already contains real Blade views:

- `resources/views/admin/dashboard.blade.php`
- `resources/views/admin/issues.blade.php`
- `resources/views/admin/redirects.blade.php`
- `resources/views/admin/scan.blade.php`
- component views
- Livewire views

Remove command:

```bash
rm resources/views/.gitkeep
```

### `lazy-seotools-docs.md`

This file duplicates README-style documentation. After the new `README.md` is accepted as the main user documentation, this file can be removed or moved into a proper `docs/` directory.

Recommended: remove it to avoid outdated parallel documentation.

Remove command:

```bash
rm lazy-seotools-docs.md
```

## Review before deleting

### `src/LazySeoToolsServiceProvider.php`

Current content is only a backward-compatible alias:

```php
class LazySeoToolsServiceProvider extends LazySeoServiceProvider {}
```

Do not delete immediately if users may still reference this provider manually.

Keep it for backward compatibility until a major version.

### `src/Facades/LazySeo.php` and `src/Facades/Seo.php`

Both facades may be public API aliases. Do not delete unless you intentionally break or deprecate one facade.

Recommended:

- keep both for now;
- document `Seo` as the shorter facade;
- document `LazySeo` as backward-compatible alias.

### `src/LazySeo.php`

Review usage before deleting. If it only exists as a legacy root class and is not used by the service container, it can be deprecated in a future major release.

### `src/Services/SeoService.php` and `src/Services/SeoManager.php`

`SeoManager` extends `SeoService`, so this is not automatically duplicated dead code. Review responsibilities before merging/removing.

## Missing but expected development files

These are not "extra", but the package references or would benefit from them:

- `tests/`
- `phpstan.neon` or `phpstan.neon.dist`
- `.github/workflows/tests.yml`

`composer.json` already contains dev dependencies and scripts for Pest/PHPStan, so adding these files should be a priority.
