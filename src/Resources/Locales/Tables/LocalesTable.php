<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Resources\Locales\Tables;

use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Tipi\Localization\Filament\Actions\ActivateLocaleAction;
use Tipi\Localization\Filament\Actions\DeactivateLocaleAction;
use Tipi\Localization\Filament\Actions\DeleteLocaleAction;
use Tipi\Localization\Filament\Actions\EditLocaleAction;
use Tipi\Localization\Filament\Actions\MakeLocaleDefaultAction;
use Tipi\Localization\Models\Locale;

final class LocalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Display order, as used everywhere else (switchers, contexts).
            ->defaultSort(fn (Builder $query): Builder => $query->ordered())
            // Persisted by ListLocales::reorderTable() through ReorderLocales.
            ->reorderable('position')
            ->columns([
                TextColumn::make('name')
                    ->label(self::label('name'))
                    ->searchable(),
                TextColumn::make('native_name')
                    ->label(self::label('native_name'))
                    ->searchable(),
                TextColumn::make('native_abbreviation')
                    ->label(self::label('native_abbreviation'))
                    ->toggleable(),
                TextColumn::make('code')
                    ->label(self::label('code'))
                    ->searchable(),
                TextColumn::make('country_code')
                    ->label(self::label('country_code'))
                    ->toggleable(),
                TextColumn::make('text_direction')
                    ->label(self::label('text_direction'))
                    ->formatStateUsing(fn (Locale $record): string => __('tipi-filament-localization::locales.text_directions.'.$record->text_direction->value))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label(self::label('status'))
                    ->state(fn (Locale $record): array => array_values(array_filter([
                        $record->isActive() ? 'active' : 'inactive',
                        $record->isDefault() ? 'default' : null,
                    ])))
                    ->formatStateUsing(fn (string $state): string => __('tipi-filament-localization::locales.statuses.'.$state))
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'info',
                        'default' => 'success',
                        default => 'gray',
                    })
                    ->badge(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditLocaleAction::make(),
                    MakeLocaleDefaultAction::make(),
                    ActivateLocaleAction::make(),
                    DeactivateLocaleAction::make(),
                    DeleteLocaleAction::make(),
                ]),
            ]);
    }

    private static function label(string $field): string
    {
        return __('tipi-filament-localization::locales.fields.'.$field);
    }
}
