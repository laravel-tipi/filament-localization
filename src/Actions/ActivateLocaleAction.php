<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Tipi\Localization\Actions\ActivateLocale;
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
            ->label('Activate')
            ->color('primary')
            ->authorize('activate')
            ->visible(
                fn (Locale $record): bool => $record->canBeActivated(),
            )
            ->action(function (Locale $record): void {
                resolve(ActivateLocale::class)->execute(
                    code: $record->getKey(),
                );

                Notification::make()
                    ->title('Locale activated')
                    ->success()
                    ->send();
            });
    }
}