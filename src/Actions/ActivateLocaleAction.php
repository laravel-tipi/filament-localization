<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Tipi\Localization\Actions\ActivateLocale;
use Tipi\Localization\Filament\Support\LocalizationOperation;
use Tipi\Localization\Models\Locale;

final class ActivateLocaleAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'activate_locale';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('tipi-filament-localization::locales.actions.activate.label'))
            ->successNotificationTitle(__('tipi-filament-localization::locales.actions.activate.success'))
            ->color('primary')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->authorize('activate')
            ->visible(fn (Locale $record): bool => $record->canBeActivated())
            ->action(fn (Locale $record) => LocalizationOperation::run(
                $this,
                fn () => resolve(ActivateLocale::class)->execute($record->code),
            ));
    }
}
