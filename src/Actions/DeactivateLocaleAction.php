<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Tipi\Localization\Actions\DeactivateLocale;
use Tipi\Localization\Filament\Support\LocalizationOperation;
use Tipi\Localization\Models\Locale;

final class DeactivateLocaleAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'deactivate_locale';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('tipi-filament-localization::locales.actions.deactivate.label'))
            ->successNotificationTitle(__('tipi-filament-localization::locales.actions.deactivate.success'))
            ->color('danger')
            ->icon(Heroicon::OutlinedNoSymbol)
            ->authorize('deactivate')
            ->visible(fn (Locale $record): bool => $record->canBeDeactivated())
            ->requiresConfirmation()
            ->action(fn (Locale $record) => LocalizationOperation::run(
                $this,
                fn () => resolve(DeactivateLocale::class)->execute($record->code),
            ));
    }
}
