<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Livewire\Drawer\Utils;
use Livewire\Livewire;
use Tipi\Localization\Facades\Localization;
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

it('selects a locale and reloads the current browser url', function (): void {
    Livewire::test(LocaleSwitcher::class)
        ->call('switchLocale', 'ka')
        ->assertJs('window.location.reload()')
        ->assertNoRedirect();

    expect(Localization::currentCode())->toBe('ka');
});

it('reloads after selecting a locale regardless of how the page was loaded', function (array $headers): void {
    Route::middleware('web')->get('/locale-switcher-test', fn (): string => Blade::render(
        '@livewire(\'tipi-filament-localization-locale-switcher\')',
    ));

    $page = $this->withHeaders($headers)->get('/locale-switcher-test?search=test&page=2');
    $page->assertOk();

    $snapshot = Utils::extractAttributeDataFromHtml($page->getContent(), 'wire:snapshot');

    // No URL is stored in client-mutable component state.
    expect($snapshot['data'])->not->toHaveKey('redirectUrl');

    $response = $this->postJson(app('livewire')->getUpdateUri(), [
        'components' => [[
            'snapshot' => json_encode($snapshot, JSON_THROW_ON_ERROR),
            'updates' => [],
            'calls' => [[
                'method' => 'switchLocale',
                'params' => ['ka'],
                'path' => '',
            ]],
        ]],
    ], ['X-Livewire' => 'true']);

    $response->assertOk()
        ->assertSessionHas(config('localization.session.key'), 'ka')
        ->assertJsonPath('components.0.effects.xjs.0.expression', 'window.location.reload()');

    // Reloading uses the browser URL (including fragments, which never reach PHP),
    // rather than the mount URL, a stale Referer, or an untrusted redirect target.
    expect($response->json('components.0.effects'))->not->toHaveKey('redirect');
})->with([
    'full page load' => [[]],
    'wire:navigate with a stale referer' => [[
        'X-Livewire-Navigate' => 'true',
        'Referer' => 'http://localhost/previous-page',
    ]],
    'untrusted referer' => [[
        'Referer' => 'https://evil.example/redirect',
    ]],
]);
