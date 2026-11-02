<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Tipi\Localization\Actions\UpdateLocale;
use Tipi\Localization\Data\UpdateLocaleData;
use Tipi\Localization\Filament\Resources\Locales\Schemas\LocaleForm;
use Tipi\Localization\Filament\Support\LocalizationOperation;
use Tipi\Localization\Models\Locale;

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
            ->label(__('tipi-filament-localization::locales.actions.edit.label'))
            ->successNotificationTitle(__('tipi-filament-localization::locales.actions.edit.success'))
            ->icon(Heroicon::OutlinedPencilSquare)
            ->authorize('update')
            ->schema(LocaleForm::edit())
            ->fillForm(fn (Locale $record): array => [
                'code' => $record->code,
                'name' => $record->name,
                'native_name' => $record->native_name,
                'native_abbreviation' => $record->native_abbreviation,
                'country_code' => $record->country_code,
                'text_direction' => $record->text_direction->value,
            ])
            ->action(fn (Locale $record, array $data) => LocalizationOperation::run(
                $this,
                fn () => resolve(UpdateLocale::class)->execute($record->code, UpdateLocaleData::fromArray($data)),
            ));
    }
}
