<?php

declare(strict_types=1);

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tipi\Localization\Filament\Livewire\LocaleSwitcher;
use Tipi\Localization\Localization;

/**
 * The snapshot of the locale switcher rendered in a full page.
 *
 * @return array<string, mixed>
 */
function switcherSnapshot(TestResponse $page): array
{
    preg_match_all('/wire:snapshot="([^"]+)"/', (string) $page->getContent(), $matches);

    foreach ($matches[1] as $encoded) {
        $snapshot = json_decode(htmlspecialchars_decode($encoded, ENT_QUOTES), true, flags: JSON_THROW_ON_ERROR);

        if ($snapshot['memo']['name'] === 'tipi-filament-localization-locale-switcher') {
            return $snapshot;
        }
    }

    throw new RuntimeException('The locale switcher was not rendered.');
}

/**
 * @param  array<string, mixed>  $snapshot
 */
function callSwitcher(array $snapshot, string $code): TestResponse
{
    // A real update request starts in a fresh application: no current
    // context and the configured application locale.
    app()->forgetScopedInstances();
    Facade::clearResolvedInstance(Localization::class);
    App::setLocale('en');

    return test()->postJson(app('livewire')->getUpdateUri(), [
        'components' => [[
            'snapshot' => json_encode($snapshot, JSON_THROW_ON_ERROR),
            'updates' => [],
            'calls' => [['method' => 'switchLocale', 'params' => [$code], 'path' => '']],
        ]],
    ], ['X-Livewire' => 'true']);
}

function currentSwitcherCode(string $html): ?string
{
    preg_match('/<button[^>]*aria-current="true"[^>]*>/', $html, $button);
    preg_match('/lang="([^"]+)"/', $button[0] ?? '', $lang);

    return $lang[1] ?? null;
}

describe('rendering', function (): void {
    it('lists the locales of its context in display order', function (): void {
        Livewire::test(LocaleSwitcher::class, ['context' => 'admin'])
            ->assertSeeInOrder(['English', 'ქართული'])
            ->assertDontSee('Русский');

        Livewire::test(LocaleSwitcher::class, ['context' => 'web'])
            ->assertSeeInOrder(['English', 'ქართული', 'Русский']);
    });

    it('uses the current context when none is given', function (): void {
        Livewire::test(LocaleSwitcher::class)
            ->assertSee('Русский');
    });

    it('marks the current locale', function (): void {
        App::setLocale('ka');

        $html = Livewire::test(LocaleSwitcher::class, ['context' => 'admin'])->html();

        expect(currentSwitcherCode($html))->toBe('ka');
    });

    it('marks the context default when the application locale is outside the context', function (): void {
        App::setLocale('ru');

        $html = Livewire::test(LocaleSwitcher::class, ['context' => 'admin'])->html();

        expect(currentSwitcherCode($html))->toBe('en');
    });

    it('renders nothing when the context offers a single locale', function (): void {
        Livewire::test(LocaleSwitcher::class, ['context' => 'kiosk'])
            ->assertDontSee('English')
            ->assertDontSeeHtml('fi-dropdown');
    });

    it('shows flags by default and a language icon without them', function (): void {
        Livewire::test(LocaleSwitcher::class, ['context' => 'web'])
            ->assertDontSeeHtml('fi-icon-btn')
            ->assertSeeHtml('aria-label="Change language"');

        Livewire::test(LocaleSwitcher::class, ['context' => 'web', 'showFlags' => false])
            ->assertSeeHtml('fi-icon-btn')
            ->assertSeeHtml('aria-label="Change language"');
    });

    it('falls back to a language icon when the current locale has no flag', function (): void {
        App::setLocale('ru');

        Livewire::test(LocaleSwitcher::class, ['context' => 'web'])
            ->assertSeeHtml('fi-icon-btn');
    });

    it('sets the language and direction of each entry', function (): void {
        Livewire::test(LocaleSwitcher::class, ['context' => 'web'])
            ->assertSeeHtml('lang="ka"')
            ->assertSeeHtml('dir="ltr"');
    });
});

describe('switching', function (): void {
    it('remembers the choice for its context and reloads the page', function (): void {
        Livewire::test(LocaleSwitcher::class, ['context' => 'admin'])
            ->call('switchLocale', 'ka')
            ->assertJs('window.location.reload()')
            ->assertNoRedirect();

        expect(Cookie::queued('locale_admin')?->getValue())->toBe('ka')
            ->and(Cookie::queued('locale_web'))->toBeNull();
    });

    it('rejects a locale outside its context', function (string $code): void {
        Livewire::test(LocaleSwitcher::class, ['context' => 'admin'])
            ->call('switchLocale', $code)
            ->assertNotified('Language unavailable')
            ->assertNoJs();

        expect(Cookie::queued('locale_admin'))->toBeNull()
            ->and(App::getLocale())->toBe('en');
    })->with(['unsupported' => 'ru', 'unknown' => 'xx', 'malformed' => '<script>']);

    it('cannot be pointed at another context by the client', function (string $property, mixed $value): void {
        Livewire::test(LocaleSwitcher::class, ['context' => 'admin'])
            ->set($property, $value);
    })->with([
        ['context', 'web'],
        ['showFlags', false],
    ])->throws(CannotUpdateLockedPropertyException::class);
});

describe('in a Filament panel', function (): void {
    it('re-applies the panel context on Livewire requests', function (): void {
        $user = $this->user();

        $snapshot = switcherSnapshot($this->actingAs($user)->get('/admin')->assertOk());

        expect($snapshot['data']['context'])->toBe('admin');

        $response = callSwitcher($snapshot, 'ka')
            ->assertOk()
            ->assertCookie('locale_admin', 'ka')
            ->assertCookieMissing('locale_web')
            ->assertJsonPath('components.0.effects.xjs.0.expression', 'window.location.reload()');

        // The persistent UseLocaleContext:admin made "admin" the current
        // context, so the choice also applied immediately.
        expect(currentSwitcherCode($response->json('components.0.effects.html')))->toBe('ka');
    });

    it('serves the next page load in the chosen locale', function (): void {
        $user = $this->user();

        $this->actingAs($user)
            ->withCookie('locale_admin', 'ka')
            ->get('/admin')
            ->assertHtmlLang('ka');
    });
});

describe('outside Filament', function (): void {
    it('reloads the browser url instead of redirecting, however the page was loaded', function (array $headers): void {
        Route::middleware('web')->get('/locale-switcher-test', fn (): string => Blade::render(
            '@livewire(\'tipi-filament-localization-locale-switcher\')',
        ));

        $page = $this->withHeaders($headers)->get('/locale-switcher-test?search=test&page=2')->assertOk();

        $snapshot = switcherSnapshot($page);

        // No URL is stored in client-mutable component state.
        expect($snapshot['data'])->not->toHaveKey('redirectUrl');

        $response = callSwitcher($snapshot, 'ka')
            ->assertOk()
            ->assertCookie('locale_web', 'ka')
            ->assertJsonPath('components.0.effects.xjs.0.expression', 'window.location.reload()');

        // The browser URL keeps the query string and fragment (which never
        // reaches PHP), unlike the mount URL, a stale Referer or an
        // untrusted redirect target.
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
});
