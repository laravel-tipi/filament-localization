<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Tipi\Localization\Actions\DeactivateLocale;
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
            ->label('Deactivate')
            ->color('danger')
            ->authorize('deactivate')
            ->visible(
                fn (Locale $record): bool => $record->canBeDeactivated(),
            )
            ->action(function (Locale $record): void {
                resolve(DeactivateLocale::class)->execute(
                    code: $record->getKey(),
                );

                Notification::make()
                    ->title('Locale deactivated')
                    ->success()
                    ->send();
            });
    }
}
