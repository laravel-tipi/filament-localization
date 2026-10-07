<?php

declare(strict_types=1);

use Illuminate\Session\Middleware\StartSession;
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
    $this->app['router']->middleware(StartSession::class)->get(
        '/locale-switcher-test',
        fn () => Livewire::test(LocaleSwitcher::class)
            ->call('switchLocale', 'ka')
            ->assertRedirect(url('/locale-switcher-test')),
    );

    $this->get('/locale-switcher-test')->assertOk();
});
