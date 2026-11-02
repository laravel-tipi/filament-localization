<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Resources\Locales;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Tipi\Localization\Config\LocalizationConfig;
use Tipi\Localization\Filament\FilamentLocalizationPlugin;
use Tipi\Localization\Filament\Resources\Locales\Pages\ListLocales;
use Tipi\Localization\Filament\Resources\Locales\Tables\LocalesTable;
use UnitEnum;

/**
 * Manages database-backed locales through the core actions. Authorized by
 * the core LocalePolicy ("manage-localization" ability by default).
 */
final class LocaleResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Language;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'locales';

    public static function getModel(): string
    {
        return resolve(LocalizationConfig::class)->localeModel;
    }

    public static function getModelLabel(): string
    {
        return __('tipi-filament-localization::locales.model.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('tipi-filament-localization::locales.model.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('tipi-filament-localization::locales.navigation.label');
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return FilamentLocalizationPlugin::currentNavigationGroup();
    }

    public static function table(Table $table): Table
    {
        return LocalesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLocales::route('/'),
        ];
    }
}
