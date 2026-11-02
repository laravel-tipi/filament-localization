<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Resources\Locales\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Tipi\Localization\Rules\LocaleRules;
use Tipi\Support\Enums\TextDirection;

/**
 * Locale form fields, validated with the core LocaleRules so errors
 * appear on the fields. The core DTOs and actions validate again.
 */
final class LocaleForm
{
    /**
     * @return list<Component>
     */
    public static function create(): array
    {
        $rules = LocaleRules::create();

        return [
            Grid::make()->schema([
                TextInput::make('code')
                    ->label(self::label('code'))
                    ->helperText(__('tipi-filament-localization::locales.helpers.code'))
                    ->required()
                    ->rules($rules['code']),
                ...self::metadata($rules),
                Toggle::make('active')
                    ->label(self::label('active'))
                    ->helperText(__('tipi-filament-localization::locales.helpers.active'))
                    ->default(false)
                    ->columnSpanFull(),
            ]),
        ];
    }

    /**
     * @return list<Component>
     */
    public static function edit(): array
    {
        return [
            Grid::make()->schema([
                TextInput::make('code')
                    ->label(self::label('code'))
                    ->disabled()
                    ->dehydrated(false),
                ...self::metadata(LocaleRules::update()),
            ]),
        ];
    }

    /**
     * @param  array<string, list<mixed>>  $rules
     * @return list<Component>
     */
    private static function metadata(array $rules): array
    {
        return [
            Select::make('text_direction')
                ->label(self::label('text_direction'))
                ->options(collect(TextDirection::cases())->mapWithKeys(fn (TextDirection $direction): array => [
                    $direction->value => __('tipi-filament-localization::locales.text_directions.'.$direction->value),
                ])->all())
                ->default(TextDirection::Ltr->value)
                ->native(false)
                ->selectablePlaceholder(false)
                ->required()
                ->rules($rules['text_direction']),
            TextInput::make('name')
                ->label(self::label('name'))
                ->required()
                ->rules($rules['name']),
            TextInput::make('native_name')
                ->label(self::label('native_name'))
                ->required()
                ->rules($rules['native_name']),
            TextInput::make('native_abbreviation')
                ->label(self::label('native_abbreviation'))
                ->rules($rules['native_abbreviation']),
            TextInput::make('country_code')
                ->label(self::label('country_code'))
                ->helperText(__('tipi-filament-localization::locales.helpers.country_code'))
                ->rules($rules['country_code']),
        ];
    }

    private static function label(string $field): string
    {
        return __('tipi-filament-localization::locales.fields.'.$field);
    }
}
