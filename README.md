# Laravel Tipi Filament Localization

Filament integration for [`laravel-tipi/localization`](https://github.com/laravel-tipi/localization).

- Runs each Filament panel in a **locale context**, so each panel has its own supported locales, default and remembered choice.
- Adds a **locale switcher** that keeps the current page and works with Livewire requests.
- Adds **management UIs** for database-backed locales and locale contexts. They are thin layers over the core actions and policies.

Locale resolution, negotiation, preferences, validation and invariants all live in the core package. This package wires them into Filament.

## Requirements

- PHP 8.5+
- Laravel 13
- Filament 5.10+
- `laravel-tipi/localization` 0.2+

## Installation

```bash
composer require laravel-tipi/filament-localization
```

Laravel discovers the service provider automatically. Configure locales and contexts in `config/localization.php` (see the [core documentation](https://github.com/laravel-tipi/localization#contexts)), then register the plugin on your panel:

```php
use Tipi\Localization\Filament\FilamentLocalizationPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugins([
            FilamentLocalizationPlugin::make()
                ->context('admin'),
        ]);
}
```

## Locale contexts

A panel runs in one locale context. Without `->context()`, the plugin uses the core `default_context` (usually `web`, which is shared with your public site).

Give an admin panel its own context so that it can offer different locales and remember its own choice:

```php
// config/localization.php
'contexts' => [
    'web' => ['locales' => '*'],
    'admin' => ['locales' => ['en', 'ka'], 'default' => 'en'],
],
```

```php
FilamentLocalizationPlugin::make()->context('admin');
```

For every panel request, including Livewire updates, the plugin:

1. makes the context current (core `UseLocaleContext` middleware);
2. sets the application locale to the visitor's remembered choice for that context, then the best `Accept-Language` match, then the context default;
3. adds `Vary: Accept-Language, Cookie` to the response.

Filament reads the application locale, so `<html lang>`, Filament's own translations and its text direction follow automatically.

### Multiple panels

Each panel registers its own plugin instance, so panels can use different contexts:

```php
// AdminPanelProvider
FilamentLocalizationPlugin::make()->context('admin');

// PartnerPanelProvider
FilamentLocalizationPlugin::make()->context('partners')->management(false);
```

Choices are remembered per context. A choice made on the public site never changes the admin panel, and the reverse also holds.

## Locale switcher

By default, the switcher is rendered before the user menu. It lists the context's supported locales in display order, shows each locale's native name and marks the current one. It renders nothing when the context offers only one locale.

Choosing a locale calls `Localization::select($code, $context)`, which remembers the choice in the context's preference cookie and applies it. The page is then reloaded from the browser's own URL, so the current page, query string (table filters, search, pagination) and fragment are kept. Locales outside the context are rejected with a notification.

```php
use Filament\View\PanelsRenderHook;

FilamentLocalizationPlugin::make()
    ->showFlags(false)                                // language icon instead of country flags
    ->renderHook(PanelsRenderHook::SIDEBAR_NAV_START) // move it
    ->switcher(false);                                // or omit it
```

Flags use the locale's `country_code` and [blade-country-flags](https://github.com/stijnvanouplines/blade-country-flags). A locale without a country code, or without a matching flag, gets a generic icon.

The switcher is a Livewire component, so you can also render it yourself:

```blade
<livewire:tipi-filament-localization-locale-switcher context="admin" :show-flags="false" />
```

## Locale and context management

When the core package stores data in the database, the plugin registers management UIs:

| Core setting | Registered | Path |
|---|---|---|
| `locales_driver => 'database'` | `LocaleResource` | `/{panel}/locales` |
| `contexts_driver => 'database'` | `ManageLocaleContexts` page | `/{panel}/locale-contexts` |

**Locales:** create (inactive unless "Active" is checked), edit, activate, deactivate, make default, delete, reorder by drag and drop, and filter by active or inactive. Form fields use the core `LocaleRules`, so errors appear on the fields.

**Contexts:** lists every context, including contexts declared in config that have no database row yet. You can create, edit and delete contexts. Contexts declared in config cannot be deleted. Choosing "All active locales" maps to `*`. The global default locale is always included, and the default select only offers the chosen locales.

Every operation calls the matching core action (`CreateLocale`, `ReorderLocales`, `UpdateLocaleContext`, …). When the core rejects an operation, for example deactivating a locale that is a context default or creating a locale whose code collides with a route segment, its message is shown as a notification.

### Authorization

The UIs use the core policies. By default they deny everything until you grant the `manage-localization` ability:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('manage-localization', fn (User $user): bool => $user->is_admin);
```

Each action checks its own ability (`create`, `update`, `activate`, `deactivate`, `makeDefault`, `reorder`, `delete`). To customize authorization, replace the policies through `config('localization.policies')`.

### Options

```php
FilamentLocalizationPlugin::make()
    ->management(false)          // no management UIs on this panel
    ->localeManagement(false)    // only hide the locales resource
    ->contextManagement(false)   // only hide the contexts page
    ->navigationGroup('Settings'); // default: "Localization"; null for none
```

## Translations

The package UI is translatable. Publish the language files to customize them or add languages:

```bash
php artisan vendor:publish --tag=tipi-filament-localization-translations
php artisan vendor:publish --tag=tipi-filament-localization-views
```

## How it works

- **Middleware order.** Filament registers a plugin's middleware when `->plugins()` is called, which can come before the panel's `->middleware([...])`. The preference cookie is encrypted, so the context middleware must run after `EncryptCookies`. The service provider adds a middleware priority entry for this, so the order of the calls in your panel provider doesn't matter.
- **Livewire requests.** Livewire re-runs "persistent" middleware for component updates, but compares them by class name without parameters. The plugin therefore registers the bare `UseLocaleContext` class as persistent, so `UseLocaleContext:admin` runs again for every Livewire request on the panel.
- **No session state.** Preferences are cookies managed by the core package, so they survive login and logout. They are per browser, not per user.

## Testing

```bash
composer check   # pint --test + pest
```

## License

MIT.
