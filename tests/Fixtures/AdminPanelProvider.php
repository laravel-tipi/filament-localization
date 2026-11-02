<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Tests\Fixtures;

use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Tipi\Localization\Filament\FilamentLocalizationPlugin;

/**
 * Runs in the "admin" locale context.
 */
final class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->pages([Dashboard::class])
            ->middleware(PanelMiddleware::web())
            ->authMiddleware(PanelMiddleware::auth())
            ->plugins([
                FilamentLocalizationPlugin::make()->context('admin'),
            ]);
    }
}
