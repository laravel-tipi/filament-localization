<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Tipi\Localization\Actions\DeleteLocale;
use Tipi\Localization\Filament\Support\LocalizationOperation;
use Tipi\Localization\Models\Locale;

final class DeleteLocaleAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'delete_locale';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('tipi-filament-localization::locales.actions.delete.label'))
            ->successNotificationTitle(__('tipi-filament-localization::locales.actions.delete.success'))
            ->color('danger')
            ->icon(Heroicon::OutlinedTrash)
            ->authorize('delete')
            ->visible(fn (Locale $record): bool => $record->canBeDeleted())
            ->requiresConfirmation()
            ->action(fn (Locale $record) => LocalizationOperation::run(
                $this,
                fn () => resolve(DeleteLocale::class)->execute($record->code),
            ));
    }
}
