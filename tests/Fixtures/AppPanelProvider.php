<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Tests\Fixtures;

use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Tipi\Localization\Filament\FilamentLocalizationPlugin;

/**
 * Uses the default ("web") context. The plugin is registered before the
 * panel middleware on purpose: its middleware must still run after
 * EncryptCookies.
 */
final class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('app')
            ->path('app')
            ->login()
            ->pages([Dashboard::class])
            ->plugins([
                FilamentLocalizationPlugin::make()->showFlags(false)->management(false),
            ])
            ->middleware(PanelMiddleware::web())
            ->authMiddleware(PanelMiddleware::auth());
    }
}
