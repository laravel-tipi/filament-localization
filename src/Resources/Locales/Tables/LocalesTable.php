<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Resources\Locales\Tables;

use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Tipi\Localization\Filament\Actions\ActivateLocaleAction;
use Tipi\Localization\Filament\Actions\DeactivateLocaleAction;
use Tipi\Localization\Filament\Actions\EditLocaleAction;
use Tipi\Localization\Filament\Actions\MakeLocaleDefaultAction;
use Tipi\Localization\Models\Locale;

final class LocalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('native_name')
                    ->searchable(),
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('country_code')
                    ->searchable(),
                TextColumn::make('status')
                    ->state(function (Locale $record): array {
                        $statuses = [
                            $record->isActive() ? 'Active' : 'Inactive',
                        ];

                        if ($record->isDefault()) {
                            $statuses[] = 'Default';
                        }

                        return $statuses;
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'info',
                        'Default' => 'success',
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
                ]),
            ]);
    }
}
