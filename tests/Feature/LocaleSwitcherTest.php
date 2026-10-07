<?php

declare(strict_types=1);

use Livewire\Livewire;
use Tipi\Localization\Filament\Livewire\LocaleSwitcher;

it('renders supported locales', function (): void {
    Livewire::test(LocaleSwitcher::class)
        ->assertSee('English')
        ->assertSee('ქართული');
});

it('can hide flags', function (): void {
    Livewire::test(LocaleSwitcher::class, [
        'showFlags' => false,
    ])
        ->assertSet('showFlags', false)
        ->assertSee('English');
});

it('selects a locale and redirects to the original url', function (): void {
    Livewire::test(LocaleSwitcher::class)
        ->set('redirectUrl', url('/locale-switcher-test'))
        ->call('switchLocale', 'ka')
        ->assertRedirect(url('/locale-switcher-test'));
});
