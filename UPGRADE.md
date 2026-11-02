# Upgrade Guide

## From 0.1 to 0.2

Version 0.2 requires `laravel-tipi/localization` **0.2**, which rewrote the runtime around locale contexts. Upgrade the core package first by following its [UPGRADE.md](https://github.com/laravel-tipi/localization/blob/main/UPGRADE.md). That covers the config, the migrations and the `manage-localization` gate.

```bash
composer require laravel-tipi/localization:^0.2 laravel-tipi/filament-localization:^0.2
```

### Panel middleware

The plugin no longer registers `SetRememberedLocale` and `RememberLocale` (removed from the core package). It registers `UseLocaleContext` instead. If you added those classes to a panel yourself, remove them.

Visiting a panel no longer writes the locale cookie. Only an explicit choice in the switcher does.

### Choose a context for each panel (recommended)

By default a panel uses the core default context (`web`), which it shares with your public site. To give the panel its own locales, default and remembered choice, declare a context and assign it:

```php
// config/localization.php
'contexts' => [
    'web' => ['locales' => '*'],
    'admin' => ['locales' => ['en', 'ka'], 'default' => 'en'],
],

// AdminPanelProvider
FilamentLocalizationPlugin::make()->context('admin'),
```

Preferences are now cookies named `{preference.cookie}_{context}` (for example `locale_admin`) instead of a session value. Existing session preferences are ignored. Users choose again once.

### Authorization

The core policies now **deny** everything by default. Without the gate below, the locales resource returns 403:

```php
Gate::define('manage-localization', fn (User $user): bool => $user->is_admin);
```

### Locale management changes

- New locales are created **inactive** unless "Active" is checked. The first locale is always active and the default.
- Rejected operations, such as deactivating a context default, now show a notification instead of failing with a server error.
- Rows can be reordered. New actions: delete, and a contexts page when `contexts_driver` is `database`.
- `native_abbreviation` is no longer cleared when a locale is edited.

### Code changes (only if you extended the package)

| Before | After |
|---|---|
| `LocaleForm::schema(codeDisabled: false)` | `LocaleForm::create()` |
| `LocaleForm::schema(codeDisabled: true)` | `LocaleForm::edit()` |
| `LocaleSwitcher` without a context | `context` property (locked); `null` means the current context |
| `LocaleResource::getModel()` via `LocaleModelResolver` | `LocalizationConfig::$localeModel` |
| Hard-coded English labels | Translation keys under `tipi-filament-localization::*` |

The `tipi-localization` plugin ID, the `showFlags()` and `renderHook()` methods, the Livewire component name and the action names are unchanged.
