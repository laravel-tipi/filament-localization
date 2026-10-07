# Laravel Tipi Filament Localization

Filament integration for `laravel-tipi/localization`.

The package adds locale management and a locale switcher to Filament panels while keeping locale resolution, persistence, validation, and domain operations in the core localization package.

## Requirements

- PHP 8.5+
- Laravel 13
- Filament 5.10+
- `laravel-tipi/localization` 0.1.16+

## Installation

```bash
composer require laravel-tipi/filament-localization
```

Laravel automatically discovers the package service provider.

Register the plugin on your Filament panel:

```php
use Tipi\Localization\Filament\FilamentLocalizationPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentLocalizationPlugin::make(),
        ]);
}
```

## Locale switcher

The plugin adds a locale switcher before the Filament user menu by default. Supported locales, the current locale, and persistence are provided by `laravel-tipi/localization`.

Flags are enabled by default. Disable them when needed:

```php
FilamentLocalizationPlugin::make()
    ->showFlags(false);
```

The switcher can be moved to another Filament panel render hook:

```php
use Filament\View\PanelsRenderHook;

FilamentLocalizationPlugin::make()
    ->renderHook(PanelsRenderHook::USER_MENU_BEFORE);
```

Selecting a locale uses `Localization::selectLocale()`, so the core localization package remembers the user's selection and the current Filament page is reloaded in that locale.

## Locale management

When `laravel-tipi/localization` uses the `database` locale driver, the plugin registers a Filament `LocaleResource`.

The resource provides locale creation and editing, activation and deactivation, default-locale selection, active/inactive filters, and authorization through the core locale policy.

When the `config` locale driver is used, the management resource is not registered. The locale switcher remains available.

The resource respects the locale model configured by `laravel-tipi/localization`.

## Middleware

The plugin registers the core `SetRememberedLocale` and `RememberLocale` middleware as persistent Filament middleware. Filament URLs remain stable while the locale preference is resolved and persisted by the core package.

## License

MIT.
