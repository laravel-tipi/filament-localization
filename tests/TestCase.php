<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Support\SupportServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Tipi\Localization\Filament\FilamentLocalizationServiceProvider;
use Tipi\Localization\LocalizationServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            LivewireServiceProvider::class,
            SupportServiceProvider::class,
            FilamentServiceProvider::class,
            LocalizationServiceProvider::class,
            FilamentLocalizationServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set(
            'app.key',
            'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=',
        );

        $app['config']->set('localization.locales_driver', 'config');
        $app['config']->set('localization.default_locale', 'en');
        $app['config']->set('localization.locales', [
            [
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'country_code' => 'GB',
                'text_direction' => 'ltr',
            ],
            [
                'code' => 'ka',
                'name' => 'Georgian',
                'native_name' => 'ქართული',
                'country_code' => 'GE',
                'text_direction' => 'ltr',
            ],
        ]);
    }
}
