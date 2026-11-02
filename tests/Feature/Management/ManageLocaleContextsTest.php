<?php

declare(strict_types=1);

use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tipi\Localization\Actions\CreateLocaleContext;
use Tipi\Localization\Data\CreateLocaleContextData;
use Tipi\Localization\Facades\Localization;
use Tipi\Localization\Filament\Pages\ManageLocaleContexts;

beforeEach(function (): void {
    Filament::setCurrentPanel('admin');
});

function contextAction(string $name, string $context): TestAction
{
    return TestAction::make($name)->table($context);
}

it('is registered for database-driven contexts', function (): void {
    expect(Filament::getPanel('admin')->getPages())->toContain(ManageLocaleContexts::class)
        ->and(Filament::getPanel('app')->getPages())->not->toContain(ManageLocaleContexts::class);
});

it('requires the manage-localization ability', function (): void {
    $this->actingAs($this->user())->get('/admin/locale-contexts')->assertForbidden();
    $this->actingAs($this->user(canManageLocalization: true))->get('/admin/locale-contexts')->assertOk();
});

describe('with access', function (): void {
    beforeEach(function (): void {
        $this->actingAs($this->user(canManageLocalization: true));
    });

    it('lists declared contexts before any row is stored', function (): void {
        Livewire::test(ManageLocaleContexts::class)
            ->assertCanSeeTableRecords(['web', 'admin', 'kiosk'])
            ->assertSee('All active locales')
            ->assertSee('Global default (en)')
            ->assertSee('Declared in config')
            ->assertSee('Default context');
    });

    it('creates a context and always includes the global default', function (): void {
        Livewire::test(ManageLocaleContexts::class)
            ->callAction('create', data: [
                'name' => 'shop',
                'all_locales' => false,
                'locales' => ['ka'],
                'default' => 'ka',
            ])
            ->assertHasNoFormErrors()
            ->assertNotified('Context created')
            ->assertCanSeeTableRecords(['shop']);

        $definition = Localization::contexts()->definition('shop');

        expect($definition->locales)->toBe(['en', 'ka'])
            ->and($definition->default)->toBe('ka')
            ->and($definition->required)->toBeFalse();
    });

    it('creates a context with every active locale', function (): void {
        Livewire::test(ManageLocaleContexts::class)
            ->callAction('create', data: ['name' => 'api', 'all_locales' => true])
            ->assertHasNoFormErrors();

        expect(Localization::context('api')->includesAllLocales())->toBeTrue()
            ->and(Localization::context('api')->defaultCode())->toBe('en');
    });

    it('validates the context name', function (string $name): void {
        Livewire::test(ManageLocaleContexts::class)
            ->callAction('create', data: ['name' => $name, 'all_locales' => true])
            ->assertHasFormErrors(['name']);
    })->with(['declared' => 'admin', 'malformed' => 'Not Valid', 'empty' => '']);

    it('stores a declared context on its first edit', function (): void {
        Livewire::test(ManageLocaleContexts::class)
            ->mountAction(contextAction('edit', 'admin'))
            ->assertSchemaStateSet(['all_locales' => false, 'locales' => ['en', 'ka'], 'default' => 'en'])
            ->fillForm(['locales' => ['en', 'ka', 'ru'], 'default' => 'ru'])
            ->callMountedAction()
            ->assertHasNoFormErrors()
            ->assertNotified('Context updated')
            ->assertSee('Stored');

        $definition = Localization::contexts()->definition('admin');

        expect($definition->persisted)->toBeTrue()
            ->and($definition->required)->toBeTrue()
            ->and(Localization::context('admin')->codes())->toBe(['en', 'ka', 'ru'])
            ->and(Localization::context('admin')->defaultCode())->toBe('ru');
    });

    it('only offers a default among the chosen locales', function (): void {
        Livewire::test(ManageLocaleContexts::class)
            ->callAction(contextAction('edit', 'admin'), data: ['all_locales' => false, 'locales' => ['en'], 'default' => 'ka'])
            ->assertHasFormErrors(['default']);

        expect(Localization::contexts()->definition('admin')->persisted)->toBeFalse();
    });

    it('only deletes contexts that are not declared in config', function (): void {
        resolve(CreateLocaleContext::class)->execute(new CreateLocaleContextData(name: 'shop'));

        Livewire::test(ManageLocaleContexts::class)
            ->assertActionHidden(contextAction('delete', 'admin'))
            ->assertActionHidden(contextAction('delete', 'web'))
            ->assertActionVisible(contextAction('delete', 'shop'))
            ->callAction(contextAction('delete', 'shop'))
            ->assertNotified('Context deleted')
            ->assertCanNotSeeTableRecords(['shop']);

        expect(Localization::contexts()->has('shop'))->toBeFalse();
    });
});
