<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Tipi\Localization\Facades\Localization;

final class LocaleSwitcher extends Component
{
    public bool $showFlags = true;

    public string $redirectUrl;

    public function mount(): void
    {
        $this->redirectUrl = url()->current();
    }

    public function switchLocale(string $code): void
    {
        Localization::selectLocale($code);

        $this->redirect($this->redirectUrl);
    }

    public function render(): View
    {
        return view(
            'tipi-filament-localization::livewire.locale-switcher',
            [
                'locales' => Localization::locales()->supported(),
                'currentLocale' => Localization::current(),
            ],
        );
    }
}
