<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Tipi\Localization\Filament\Livewire\LocaleSwitcher;

final class FilamentLocalizationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(
            __DIR__.'/../resources/views',
            'tipi-filament-localization',
        );

        Livewire::component(
            'tipi-filament-localization-locale-switcher',
            LocaleSwitcher::class,
        );
    }
}
