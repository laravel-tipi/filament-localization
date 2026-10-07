<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Tipi\Localization\Actions\CreateLocale;
use Tipi\Localization\Data\CreateLocaleData;
use Tipi\Localization\Filament\Resources\Locales\LocaleResource;
use Tipi\Localization\Filament\Resources\Locales\Schemas\LocaleForm;
use Tipi\Localization\Rules\LocaleRules;
use Tipi\Support\Validation\Validator;

final class CreateLocaleAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'create';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Create locale')
            ->color('primary')
            ->authorize('create', LocaleResource::getModel())
            ->icon(Heroicon::OutlinedPlus)
            ->schema(LocaleForm::schema())
            ->action(function (array $data): void {
                $validated = Validator::validate(
                    data: $data,
                    rules: LocaleRules::create(),
                    attributes: LocaleRules::attributes(),
                    path: 'mountedActions.0.data',
                );

                resolve(CreateLocale::class)->execute(
                    CreateLocaleData::fromArray($validated),
                );

                Notification::make()
                    ->title('Locale created')
                    ->success()
                    ->send();
            });
    }
}
