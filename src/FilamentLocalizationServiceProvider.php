<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament;

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Tipi\Localization\Filament\Livewire\LocaleSwitcher;
use Tipi\Localization\Http\Middleware\UseLocaleContext;

final class FilamentLocalizationServiceProvider extends ServiceProvider
{
    public const string NAMESPACE = 'tipi-filament-localization';

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', self::NAMESPACE);
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', self::NAMESPACE);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/lang' => $this->app->langPath('vendor/'.self::NAMESPACE),
            ], self::NAMESPACE.'-translations');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/'.self::NAMESPACE),
            ], self::NAMESPACE.'-views');
        }

        Livewire::component(self::NAMESPACE.'-locale-switcher', LocaleSwitcher::class);

        $this->callAfterResolving(Router::class, $this->prioritizeContextMiddleware(...));
    }

    /**
     * Filament registers a plugin's middleware when ->plugins() is called,
     * which can be before the panel's own ->middleware([...]) list. The
     * context middleware reads the (encrypted) preference cookie, so it
     * must run after EncryptCookies. A middleware priority entry makes
     * Laravel sort it there, whatever the registration order.
     */
    private function prioritizeContextMiddleware(Router $router): void
    {
        $priority = $router->middlewarePriority;

        if (in_array(UseLocaleContext::class, $priority, true)) {
            return;
        }

        $index = array_search(EncryptCookies::class, $priority, true);

        if ($index === false) {
            return;
        }

        array_splice($priority, $index + 1, 0, [UseLocaleContext::class]);

        $router->middlewarePriority = $priority;
    }
}
