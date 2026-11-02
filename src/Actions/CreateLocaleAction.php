<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Tipi\Localization\Actions\CreateLocale;
use Tipi\Localization\Data\CreateLocaleData;
use Tipi\Localization\Filament\Resources\Locales\LocaleResource;
use Tipi\Localization\Filament\Resources\Locales\Schemas\LocaleForm;
use Tipi\Localization\Filament\Support\LocalizationOperation;

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
            ->label(__('tipi-filament-localization::locales.actions.create.label'))
            ->modalHeading(__('tipi-filament-localization::locales.actions.create.label'))
            ->successNotificationTitle(__('tipi-filament-localization::locales.actions.create.success'))
            ->icon(Heroicon::OutlinedPlus)
            ->authorize('create', LocaleResource::getModel())
            ->schema(LocaleForm::create())
            ->action(fn (array $data) => LocalizationOperation::run(
                $this,
                fn () => resolve(CreateLocale::class)->execute(CreateLocaleData::fromArray($data)),
            ));
    }
}
