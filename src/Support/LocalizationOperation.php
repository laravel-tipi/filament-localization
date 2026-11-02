<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Support;

use Closure;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Tipi\Localization\Exceptions\LocalizationException;

/**
 * Runs a core management action from a Filament action. Rejected
 * operations (whose messages are written for end users) are shown as a
 * danger notification instead of failing the request.
 *
 * @internal
 */
final class LocalizationOperation
{
    public static function run(Action $action, Closure $operation): void
    {
        try {
            $operation();
        } catch (LocalizationException $exception) {
            Notification::make()
                ->danger()
                ->title($action->getFailureNotificationTitle() ?? __('tipi-filament-localization::locales.failure'))
                ->body($exception->getMessage())
                ->send();

            // Keeps form modals open so the input can be corrected.
            $action->halt();
        }

        $action->success();
    }
}
