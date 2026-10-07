<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Tipi\Localization\Config\LocalizationConfig;
use Tipi\Localization\Enums\LocaleDriver;
use Tipi\Localization\Filament\Resources\Locales\LocaleResource;
use Tipi\Localization\Http\Middleware\RememberLocale;
use Tipi\Localization\Http\Middleware\SetRememberedLocale;

final class FilamentLocalizationPlugin implements Plugin
{
    private bool $showFlags = true;

    private string $renderHook = PanelsRenderHook::USER_MENU_BEFORE;

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'tipi-localization';
    }

    public function showFlags(bool $show = true): static
    {
        $this->showFlags = $show;

        return $this;

    }

    public function renderHook(string $hook): static
    {
        $this->renderHook = $hook;

        return $this;
    }

    public function register(Panel $panel): void
    {
        $panel->middleware([
            SetRememberedLocale::class,
            RememberLocale::class,
        ], isPersistent: true);

        $panel->renderHook(
            name: $this->renderHook,
            hook: fn (): string => Blade::render(
                '<livewire:tipi-filament-localization-locale-switcher
                 :show-flags="$showFlags"
                 />',
                [
                    'showFlags' => $this->showFlags,
                ],
            ),
        );

        if (
            resolve(LocalizationConfig::class)->localesDriver
            !== LocaleDriver::Database
        ) {
            return;
        }

        $panel->resources([
            LocaleResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
