# Changelog

## Unreleased (0.2.0)

Requires `laravel-tipi/localization` ^0.2. See [UPGRADE.md](UPGRADE.md).

### Added
- `FilamentLocalizationPlugin::context()`: each panel runs in a locale context (core `UseLocaleContext`), on full page loads and Livewire requests.
- Locale contexts management page (`ManageLocaleContexts`) for the `database` contexts driver.
- Delete locale action, an "Active" toggle when creating a locale, and drag-and-drop reordering through `ReorderLocales`.
- `switcher()`, `management()`, `localeManagement()`, `contextManagement()`, `navigationGroup()` and `FilamentLocalizationPlugin::get()`.
- Translatable UI (`tipi-filament-localization::*`), with publishable translations and views.
- Rejected core operations are shown as notifications.

### Changed
- The switcher lists the panel context's locales, remembers the choice per context (cookie), and renders nothing for a single-locale context.
- Locale forms validate with the core `LocaleRules` on the fields themselves.
- `LocaleForm::schema()` replaced by `LocaleForm::create()` and `LocaleForm::edit()`.
- The switcher's `context` and `showFlags` properties are locked.

### Removed
- Use of the removed core APIs: `SetRememberedLocale`, `RememberLocale`, `LocaleModelResolver`, `LocaleDriver`, `selectLocale()` and `supported()`.

### Fixed
- Editing a locale cleared its native abbreviation.
- The preference cookie was ignored when `->plugins()` was called before `->middleware()` on the panel.
- Selecting an unsupported locale caused a server error.
