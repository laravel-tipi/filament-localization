<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Resources\Locales;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Tipi\Localization\Filament\Resources\Locales\Pages\ListLocales;
use Tipi\Localization\Filament\Resources\Locales\Tables\LocalesTable;
use Tipi\Localization\LocaleModelResolver;
use UnitEnum;

final class LocaleResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Language;

    protected static string|UnitEnum|null $navigationGroup = 'Localization';

    protected static ?string $recordTitleAttribute = 'code';

    public static function getModel(): string
    {
        return resolve(LocaleModelResolver::class)->class();
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
