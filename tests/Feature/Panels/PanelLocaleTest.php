<?php

declare(strict_types=1);

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Tipi\Localization\Http\Middleware\UseLocaleContext;

it('serves the panel in the context default without a preference', function (): void {
    $this->get('/admin/login')
        ->assertOk()
        ->assertHtmlLang('en');
});

it('negotiates Accept-Language within the panel context', function (string $header, string $expected): void {
    $this->withHeader('Accept-Language', $header)
        ->get('/admin/login')
        ->assertOk()
        ->assertHtmlLang($expected);
})->with([
    'supported' => ['ka-GE,ka;q=0.9', 'ka'],
    'outside the admin context' => ['ru', 'en'],
    'unknown' => ['fr-FR', 'en'],
]);

it('applies the preference remembered for the panel context', function (): void {
    $this->withCookie('locale_admin', 'ka')
        ->withHeader('Accept-Language', 'en')
        ->get('/admin/login')
        ->assertHtmlLang('ka');
});

it('keeps preferences separate per context', function (): void {
    // A public-site ("web") choice never affects the admin panel...
    $this->withCookie('locale_web', 'ka')
        ->get('/admin/login')
        ->assertHtmlLang('en');

    // ...and the app panel (default "web" context) uses it.
    $this->withCookie('locale_web', 'ka')
        ->get('/app/login')
        ->assertHtmlLang('ka');
});

it('ignores a remembered locale the context does not support', function (string $cookie): void {
    $this->withCookie('locale_admin', $cookie)
        ->get('/admin/login')
        ->assertOk()
        ->assertHtmlLang('en');
})->with(['ru', 'xx', '../../etc']);

it('reads the encrypted preference even when the plugin is registered before the panel middleware', function (): void {
    // The "app" panel calls ->plugins() before ->middleware(), so the
    // context middleware is listed before EncryptCookies.
    $route = collect(Route::getRoutes()->getRoutes())->first(fn ($route): bool => $route->getName() === 'filament.app.auth.login');
    $middleware = app(Router::class)->gatherRouteMiddleware($route);

    expect(array_search(UseLocaleContext::class, $middleware, true))
        ->toBeGreaterThan(array_search(EncryptCookies::class, $middleware, true));

    $this->withCookie('locale_web', 'ru')
        ->get('/app/login')
        ->assertHtmlLang('ru');
});

it('varies panel responses by language and cookie', function (): void {
    $vary = $this->get('/admin/login')->headers->all('vary');

    expect($vary)->toContain('Accept-Language')->toContain('Cookie');
});

it('keeps the remembered locale across login and logout', function (): void {
    $user = $this->user();

    $this->withCookie('locale_admin', 'ka')
        ->actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertHtmlLang('ka');

    auth()->logout();

    $this->withCookie('locale_admin', 'ka')
        ->get('/admin/login')
        ->assertHtmlLang('ka');
});

// Filament registers render hooks globally when a panel boots, so each
// test requests a single panel (as a real request does).
it('renders the switcher with the panel context locales only', function (): void {
    $this->actingAs($this->user())
        ->get('/admin')
        ->assertOk()
        ->assertSee('ქართული')
        ->assertDontSee('Русский');
});

it('renders the switcher with the default context locales', function (): void {
    $this->actingAs($this->user())
        ->get('/app')
        ->assertOk()
        ->assertSee('ქართული')
        ->assertSee('Русский');
});
