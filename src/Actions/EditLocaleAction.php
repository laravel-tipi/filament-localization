<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Tipi\Localization\Actions\UpdateLocale;
use Tipi\Localization\Data\UpdateLocaleData;
use Tipi\Localization\Filament\Resources\Locales\Schemas\LocaleForm;
use Tipi\Localization\Models\Locale;
use Tipi\Localization\Rules\LocaleRules;
use Tipi\Support\Validation\Validator;

final class EditLocaleAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'edit';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Edit')
            ->color('primary')
            ->authorize('update')
            ->icon(Heroicon::OutlinedPencilSquare)
            ->tableIcon(Heroicon::OutlinedPencilSquare)
            ->schema(LocaleForm::schema(codeDisabled: true))
            ->fillForm(
                fn (Locale $record): array => [
                    'code' => $record->code,
                    'name' => $record->name,
                    'native_name' => $record->native_name,
                    'country_code' => $record->country_code,
                    'text_direction' => $record->text_direction->value,
                ],
            )
            ->action(function (Locale $record, array $data): void {
                $validated = Validator::validate(
                    data: $data,
                    rules: LocaleRules::update(),
                    attributes: LocaleRules::attributes(),
                    path: 'mountedActions.0.data',
                );
                resolve(UpdateLocale::class)->execute(
                    code: $record->getKey(),
                    data: UpdateLocaleData::fromArray($validated),
                );

                Notification::make()
                    ->title('Locale updated')
                    ->success()
                    ->send();
            });
    }
}
