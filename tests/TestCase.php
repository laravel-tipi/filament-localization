<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Testing\TestResponse;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use PHPUnit\Framework\Assert;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;
use StijnVanouplines\BladeCountryFlags\BladeCountryFlagsServiceProvider;
use Tipi\Localization\Filament\FilamentLocalizationServiceProvider;
use Tipi\Localization\Filament\Tests\Fixtures\AdminPanelProvider;
use Tipi\Localization\Filament\Tests\Fixtures\AppPanelProvider;
use Tipi\Localization\Filament\Tests\Fixtures\User;
use Tipi\Localization\LocalizationServiceProvider;
use Tipi\Localization\Policies\LocalePolicy;

/**
 * Config-driven locales and contexts:
 *
 * - locales: en (global default), ka, ru;
 * - "web" (default context): every active locale;
 * - "admin": en and ka, defaulting to en;
 * - "kiosk": en only.
 *
 * Panels: "admin" (context "admin") at /admin, "app" (default context) at /app.
 */
abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            BladeCaptureDirectiveServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            BladeCountryFlagsServiceProvider::class,
            SupportServiceProvider::class,
            ActionsServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            NotificationsServiceProvider::class,
            SchemasServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            FilamentServiceProvider::class,
            // After Filament, as package discovery orders them in an app:
            // Livewire wires up the component hooks registered before it
            // boots, Filament's partial rendering among them.
            LivewireServiceProvider::class,
            LocalizationServiceProvider::class,
            FilamentLocalizationServiceProvider::class,
            AdminPanelProvider::class,
            AppPanelProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $config = $app['config'];

        $config->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
        $config->set('auth.providers.users.model', User::class);
        $config->set('database.default', 'testing');

        $config->set('localization.locales_driver', 'config');
        $config->set('localization.contexts_driver', 'config');
        $config->set('localization.default_locale', 'en');
        $config->set('localization.locales', [
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'native_abbreviation' => 'EN', 'country_code' => 'GB'],
            ['code' => 'ka', 'name' => 'Georgian', 'native_name' => 'ქართული', 'native_abbreviation' => 'ქა', 'country_code' => 'GE'],
            ['code' => 'ru', 'name' => 'Russian', 'native_name' => 'Русский', 'native_abbreviation' => 'РУ'],
        ]);
        $config->set('localization.contexts', self::contexts());
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected static function contexts(): array
    {
        return [
            'web' => ['locales' => '*'],
            'admin' => ['locales' => ['en', 'ka'], 'default' => 'en'],
            'kiosk' => ['locales' => ['en']],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // The <html lang> attribute of a full Filament page.
        TestResponse::macro('assertHtmlLang', function (string $locale): TestResponse {
            /** @var TestResponse $this */
            preg_match('/<html\s+lang="([^"]*)"/', (string) $this->getContent(), $matches);

            Assert::assertSame($locale, $matches[1] ?? null, 'Unexpected <html lang> attribute.');

            return $this;
        });
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadLaravelMigrations();
    }

    protected function user(bool $canManageLocalization = false): User
    {
        Gate::define(LocalePolicy::ABILITY, fn (User $user): bool => $user->email === 'admin@example.com');

        return User::query()->firstOrCreate(
            ['email' => $canManageLocalization ? 'admin@example.com' : 'editor@example.com'],
            ['name' => 'Test', 'password' => 'secret'],
        );
    }
}
