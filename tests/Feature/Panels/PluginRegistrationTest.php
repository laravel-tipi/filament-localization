<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Livewire\Mechanisms\PersistentMiddleware\PersistentMiddleware;
use Tipi\Localization\Filament\FilamentLocalizationPlugin;
use Tipi\Localization\Filament\Pages\ManageLocaleContexts;
use Tipi\Localization\Filament\Resources\Locales\LocaleResource;
use Tipi\Localization\Http\Middleware\UseLocaleContext;

it('is registered on the panel under a stable id', function (): void {
    $plugin = Filament::getPanel('admin')->getPlugin('tipi-localization');

    expect($plugin)->toBeInstanceOf(FilamentLocalizationPlugin::class)
        ->and($plugin->getId())->toBe(FilamentLocalizationPlugin::ID);
});

it('runs the panel in the configured locale context', function (): void {
    expect(Filament::getPanel('admin')->getMiddleware())
        ->toContain(UseLocaleContext::with('admin'))
        ->and(Filament::getPanel('admin')->getPlugin('tipi-localization')->getContext())->toBe('admin');
});

it('uses the default context when none is configured', function (): void {
    $plugin = Filament::getPanel('app')->getPlugin('tipi-localization');

    expect(Filament::getPanel('app')->getMiddleware())->toContain(UseLocaleContext::class)
        ->and($plugin->getContext())->toBe('web');
});

it('registers the bare context middleware as Livewire persistent middleware', function (): void {
    // Livewire compares persistent middleware without parameters, so
    // "UseLocaleContext:admin" only persists when the class is registered.
    expect(app(PersistentMiddleware::class)->getPersistentMiddleware())
        ->toContain(UseLocaleContext::class)
        ->not->toContain(UseLocaleContext::with('admin'));
});

it('resolves the plugin of the current panel', function (): void {
    Filament::setCurrentPanel('admin');
    expect(FilamentLocalizationPlugin::get()->getContext())->toBe('admin');

    Filament::setCurrentPanel('app');
    expect(FilamentLocalizationPlugin::get()->getContext())->toBe('web');
});

/**
 * @return array<string, mixed>
 */
function renderHooks(Panel $panel): array
{
    return (fn (): array => $this->renderHooks)->call($panel);
}

it('adds the switcher to the configured render hook', function (): void {
    $panel = Panel::make()->id('custom');

    FilamentLocalizationPlugin::make()
        ->renderHook(PanelsRenderHook::SIDEBAR_NAV_START)
        ->register($panel);

    expect(renderHooks($panel))->toHaveKey(PanelsRenderHook::SIDEBAR_NAV_START)
        ->not->toHaveKey(PanelsRenderHook::USER_MENU_BEFORE);

    $hook = array_values(renderHooks($panel)[PanelsRenderHook::SIDEBAR_NAV_START])[0][0];

    expect($hook()->getData())->toBe(['context' => 'web', 'showFlags' => true]);
});

it('can omit the switcher', function (): void {
    $panel = Panel::make()->id('custom');

    FilamentLocalizationPlugin::make()->switcher(false)->register($panel);

    expect(renderHooks($panel))->toBe([]);
});

it('does not register management UIs for config-driven locales and contexts', function (): void {
    expect(Filament::getPanel('admin')->getResources())->not->toContain(LocaleResource::class)
        ->and(Filament::getPanel('admin')->getPages())->not->toContain(ManageLocaleContexts::class);
});

it('exposes its configuration', function (): void {
    $plugin = FilamentLocalizationPlugin::make()
        ->context('shop')
        ->showFlags(false)
        ->navigationGroup('Settings');

    expect($plugin->getContext())->toBe('shop')
        ->and($plugin->shouldShowFlags())->toBeFalse()
        ->and($plugin->hasSwitcher())->toBeTrue()
        ->and($plugin->getRenderHook())->toBe(PanelsRenderHook::USER_MENU_BEFORE)
        ->and($plugin->getNavigationGroup())->toBe('Settings')
        ->and(FilamentLocalizationPlugin::make()->navigationGroup(null)->getNavigationGroup())->toBeNull()
        ->and(FilamentLocalizationPlugin::make()->getNavigationGroup())->toBe('Localization');
});

it('falls back to the default navigation group on panels without the plugin', function (): void {
    Filament::setCurrentPanel(Panel::make()->id('bare'));

    expect(FilamentLocalizationPlugin::currentNavigationGroup())->toBe('Localization');

    Filament::setCurrentPanel('admin');

    expect(FilamentLocalizationPlugin::currentNavigationGroup())->toBe('Localization');
});
