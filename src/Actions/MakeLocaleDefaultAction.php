<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Tipi\Localization\Actions\MakeLocaleDefault;
use Tipi\Localization\Models\Locale;

final class MakeLocaleDefaultAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'make_locale_default';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Make default')
            ->color('primary')
            ->authorize('makeDefault')
            ->visible(
                fn (Locale $record): bool => $record->canBeMadeDefault(),
            )
            ->action(function (Locale $record): void {
                resolve(MakeLocaleDefault::class)->execute(
                    code: $record->getKey(),
                );

                Notification::make()
                    ->title('Default locale updated')
                    ->success()
                    ->send();
            });
    }
}