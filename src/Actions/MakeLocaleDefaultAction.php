<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Tipi\Localization\Actions\MakeLocaleDefault;
use Tipi\Localization\Filament\Support\LocalizationOperation;
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
            ->label(__('tipi-filament-localization::locales.actions.make_default.label'))
            ->successNotificationTitle(__('tipi-filament-localization::locales.actions.make_default.success'))
            ->color('primary')
            ->icon(Heroicon::OutlinedStar)
            ->authorize('makeDefault')
            ->visible(fn (Locale $record): bool => $record->canBeMadeDefault())
            ->requiresConfirmation()
            ->action(fn (Locale $record) => LocalizationOperation::run(
                $this,
                fn () => resolve(MakeLocaleDefault::class)->execute($record->code),
            ));
    }
}
