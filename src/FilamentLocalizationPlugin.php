<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Tipi\Localization\Config\LocalizationConfig;
use Tipi\Localization\Enums\StorageDriver;
use Tipi\Localization\Filament\Pages\ManageLocaleContexts;
use Tipi\Localization\Filament\Resources\Locales\LocaleResource;
use Tipi\Localization\Http\Middleware\UseLocaleContext;
use UnitEnum;

/**
 * Connects a Filament panel to a Tipi locale context:
 *
 * - every panel request (including Livewire updates) runs in the context,
 *   with the visitor's remembered or negotiated locale;
 * - the locale switcher offers the context's locales and remembers the
 *   choice for that context;
 * - locale and context management UIs are registered when the core
 *   package stores them in the database.
 */
final class FilamentLocalizationPlugin implements Plugin
{
    use EvaluatesClosures;

    public const string ID = 'tipi-localization';

    private ?string $context = null;

    private bool $hasSwitcher = true;

    private bool $showFlags = true;

    private string $renderHook = PanelsRenderHook::USER_MENU_BEFORE;

    private bool $hasLocaleManagement = true;

    private bool $hasContextManagement = true;

    private string|UnitEnum|Closure|null $navigationGroup = null;

    private bool $hasCustomNavigationGroup = false;

    public static function make(): static
    {
        return app(self::class);
    }

    /**
     * The plugin registered on the current panel.
     */
    public static function get(): static
    {
        /** @var static */
        return filament(self::ID);
    }

    /**
     * The navigation group of the current panel's plugin, or the default
     * group when the panel does not use the plugin (for example when a
     * resource is registered on it by hand).
     */
    public static function currentNavigationGroup(): string|UnitEnum|null
    {
        $panel = Filament::getCurrentPanel();

        return $panel !== null && $panel->hasPlugin(self::ID)
            ? self::get()->getNavigationGroup()
            : __('tipi-filament-localization::locales.navigation.group');
    }

    public function getId(): string
    {
        return self::ID;
    }

    /**
     * The locale context the panel runs in. Defaults to the core
     * package's default context (config "default_context").
     */
    public function context(?string $context): static
    {
        $this->context = $context;

        return $this;
    }

    public function getContext(): string
    {
        return $this->context ?? resolve(LocalizationConfig::class)->defaultContext;
    }

    public function switcher(bool $condition = true): static
    {
        $this->hasSwitcher = $condition;

        return $this;
    }

    public function hasSwitcher(): bool
    {
        return $this->hasSwitcher;
    }

    public function showFlags(bool $condition = true): static
    {
        $this->showFlags = $condition;

        return $this;
    }

    public function shouldShowFlags(): bool
    {
        return $this->showFlags;
    }

    public function renderHook(string $hook): static
    {
        $this->renderHook = $hook;

        return $this;
    }

    public function getRenderHook(): string
    {
        return $this->renderHook;
    }

    /**
     * Register the locale and context management UIs (when their core
     * driver is "database"). Disable on panels that should only switch.
     */
    public function management(bool $condition = true): static
    {
        $this->hasLocaleManagement = $condition;
        $this->hasContextManagement = $condition;

        return $this;
    }

    public function localeManagement(bool $condition = true): static
    {
        $this->hasLocaleManagement = $condition;

        return $this;
    }

    public function contextManagement(bool $condition = true): static
    {
        $this->hasContextManagement = $condition;

        return $this;
    }

    public function navigationGroup(string|UnitEnum|Closure|null $group): static
    {
        $this->navigationGroup = $group;
        $this->hasCustomNavigationGroup = true;

        return $this;
    }

    public function getNavigationGroup(): string|UnitEnum|null
    {
        if (! $this->hasCustomNavigationGroup) {
            return __('tipi-filament-localization::locales.navigation.group');
        }

        return $this->evaluate($this->navigationGroup);
    }

    public function register(Panel $panel): void
    {
        // The context name is resolved per request, so the plugin can be
        // configured before the application is fully booted.
        $panel->middleware([
            $this->context === null ? UseLocaleContext::class : UseLocaleContext::with($this->context),
        ]);

        // Livewire compares persistent middleware by class name (without
        // parameters), so the bare class must be registered for
        // UseLocaleContext:{context} to run again on Livewire requests.
        $panel->persistentMiddleware([UseLocaleContext::class]);

        if ($this->hasSwitcher) {
            $panel->renderHook(
                $this->renderHook,
                fn (): View => view('tipi-filament-localization::locale-switcher', [
                    'context' => $this->getContext(),
                    'showFlags' => $this->showFlags,
                ]),
            );
        }

        $config = resolve(LocalizationConfig::class);

        if ($this->hasLocaleManagement && $config->localesDriver === StorageDriver::Database) {
            $panel->resources([LocaleResource::class]);
        }

        if ($this->hasContextManagement && $config->contextsDriver === StorageDriver::Database) {
            $panel->pages([ManageLocaleContexts::class]);
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
