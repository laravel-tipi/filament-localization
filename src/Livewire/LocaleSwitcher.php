<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Livewire;

use BladeUI\Icons\Exceptions\SvgNotFound;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Tipi\Localization\Facades\Localization;
use Tipi\Localization\LocaleContext;
use Tipi\Support\Locale;

/**
 * Lists the locales of one context and remembers the chosen one for that
 * context. The page is then reloaded from the browser's own URL, so the
 * query string and fragment are kept.
 */
final class LocaleSwitcher extends Component
{
    #[Locked]
    public ?string $context = null;

    #[Locked]
    public bool $showFlags = true;

    public function switchLocale(string $code): void
    {
        $context = $this->localeContext();

        if (! $context->supports($code)) {
            Notification::make()
                ->danger()
                ->title(__('tipi-filament-localization::switcher.unavailable.title'))
                ->body(__('tipi-filament-localization::switcher.unavailable.body'))
                ->send();

            return;
        }

        Localization::select($code, $context->name);

        $this->js('window.location.reload()');
    }

    public function render(): View
    {
        $context = $this->localeContext();
        $current = Localization::currentCode();

        $current = $context->supports($current) ? $context->get($current) : $context->default();

        return view('tipi-filament-localization::livewire.locale-switcher', [
            'current' => $current,
            'currentFlag' => $this->flag($current, '1x1'),
            'locales' => $context->supported()->values()->map(fn (Locale $locale): array => [
                'locale' => $locale,
                'current' => $locale->code === $current->code,
                'flag' => $this->flag($locale, '4x3'),
            ]),
        ]);
    }

    private function localeContext(): LocaleContext
    {
        return Localization::context($this->context);
    }

    private function flag(Locale $locale, string $ratio): ?string
    {
        if (! $this->showFlags || $locale->countryCode === null) {
            return null;
        }

        try {
            return svg('flag-'.$ratio.'-'.strtolower($locale->countryCode))->toHtml();
        } catch (SvgNotFound) {
            return null;
        }
    }
}
