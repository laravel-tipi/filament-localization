<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Tests;

use Tipi\Localization\Actions\CreateLocale;
use Tipi\Localization\Data\CreateLocaleData;

/**
 * Database-driven locales and contexts, seeded with en (default), ka and
 * ru, all active. The config contexts become "declared" contexts.
 */
abstract class DatabaseTestCase extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('localization.locales_driver', 'database');
        $app['config']->set('localization.contexts_driver', 'database');
    }

    protected function defineDatabaseMigrations(): void
    {
        parent::defineDatabaseMigrations();

        $this->loadMigrationsFrom(dirname(__DIR__).'/vendor/laravel-tipi/localization/database/migrations');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $create = $this->app->make(CreateLocale::class);

        $create->execute(new CreateLocaleData(code: 'en', name: 'English', nativeName: 'English', nativeAbbreviation: 'EN', countryCode: 'GB'));
        $create->execute(new CreateLocaleData(code: 'ka', name: 'Georgian', nativeName: 'ქართული', countryCode: 'GE', active: true));
        $create->execute(new CreateLocaleData(code: 'ru', name: 'Russian', nativeName: 'Русский', active: true));
    }
}
