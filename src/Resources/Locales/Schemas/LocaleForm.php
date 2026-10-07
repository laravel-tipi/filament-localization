<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Resources\Locales\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Tipi\Support\Enums\TextDirection;

final class LocaleForm
{
    public static function schema(bool $codeDisabled = false): array
    {
        return [
            Grid::make()
                ->schema([
                    TextInput::make('code')
                        ->label('Locale code')
                        ->required(! $codeDisabled)
                        ->disabled($codeDisabled),

                    Select::make('text_direction')
                        ->label('Text direction')
                        ->options([
                            TextDirection::Ltr->value => 'Left to right',
                            TextDirection::Rtl->value => 'Right to left',
                        ])
                        ->default(TextDirection::Ltr->value)
                        ->native(false)
                        ->required(),

                    TextInput::make('name')
                        ->required(),

                    TextInput::make('native_name')
                        ->label('Native name')
                        ->required(),

                    TextInput::make('country_code')
                        ->label('Country code'),
                ]),
        ];
    }
}
