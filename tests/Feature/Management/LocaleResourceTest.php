<?php

declare(strict_types=1);

use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tipi\Localization\Actions\DeactivateLocale;
use Tipi\Localization\Actions\UpdateLocaleContext;
use Tipi\Localization\Data\UpdateLocaleContextData;
use Tipi\Localization\Facades\Localization;
use Tipi\Localization\Filament\Resources\Locales\LocaleResource;
use Tipi\Localization\Filament\Resources\Locales\Pages\ListLocales;
use Tipi\Localization\Models\Locale;
use Tipi\Localization\Policies\LocalePolicy;

function locale(string $code): Locale
{
    return Locale::query()->findOrFail($code);
}

beforeEach(function (): void {
    Filament::setCurrentPanel('admin');
});

describe('access', function (): void {
    it('is registered for database-driven locales', function (): void {
        expect(Filament::getPanel('admin')->getResources())->toContain(LocaleResource::class)
            ->and(LocaleResource::getModel())->toBe(Locale::class)
            ->and(LocaleResource::getNavigationGroup())->toBe('Localization');
    });

    it('is not registered on panels without management', function (): void {
        expect(Filament::getPanel('app')->getResources())->not->toContain(LocaleResource::class);
    });

    it('requires the manage-localization ability', function (): void {
        $this->actingAs($this->user())->get('/admin/locales')->assertForbidden();
        $this->actingAs($this->user(canManageLocalization: true))->get('/admin/locales')->assertOk();
    });

    it('lets the application replace the ability check', function (): void {
        $this->actingAs($this->user());

        Gate::before(fn (): bool => true);

        $this->get('/admin/locales')->assertOk();
    });
});

describe('listing', function (): void {
    beforeEach(function (): void {
        $this->actingAs($this->user(canManageLocalization: true));
    });

    it('lists locales in display order', function (): void {
        Livewire::test(ListLocales::class)
            ->assertCanSeeTableRecords([locale('en'), locale('ka'), locale('ru')], inOrder: true)
            ->assertSeeInOrder(['Active', 'Default']);
    });

    it('filters active and inactive locales', function (): void {
        resolve(DeactivateLocale::class)->execute('ru');

        Livewire::test(ListLocales::class)
            ->set('activeTab', 'inactive')
            ->assertCanSeeTableRecords([locale('ru')])
            ->assertCanNotSeeTableRecords([locale('en'), locale('ka')]);
    });

    it('reorders through the core action', function (): void {
        Livewire::test(ListLocales::class)
            ->call('reorderTable', ['ru', 'en', 'ka']);

        expect(Localization::locales()->all()->keys()->all())->toBe(['ru', 'en', 'ka'])
            ->and(locale('ru')->position)->toBe(0);
    });

    it('does not reorder without the reorder ability', function (): void {
        Gate::define('manage-localization', fn (): bool => true);
        Gate::policy(Locale::class, ReorderDeniedPolicy::class);

        Livewire::test(ListLocales::class)
            ->call('reorderTable', ['ru', 'en', 'ka']);

        expect(Localization::locales()->all()->keys()->all())->toBe(['en', 'ka', 'ru']);
    });
});

describe('creating', function (): void {
    beforeEach(function (): void {
        $this->actingAs($this->user(canManageLocalization: true));
    });

    it('creates an inactive locale by default', function (): void {
        Livewire::test(ListLocales::class)
            ->callAction('create', data: [
                'code' => 'fr',
                'name' => 'French',
                'native_name' => 'Français',
                'native_abbreviation' => 'FR',
                'country_code' => 'FR',
                'text_direction' => 'ltr',
            ])
            ->assertHasNoFormErrors()
            ->assertNotified('Locale created');

        expect(locale('fr'))
            ->native_abbreviation->toBe('FR')
            ->is_active->toBeFalse()
            ->is_default->toBeFalse();
    });

    it('can create an active right-to-left locale', function (): void {
        Livewire::test(ListLocales::class)
            ->callAction('create', data: [
                'code' => 'ar',
                'name' => 'Arabic',
                'native_name' => 'العربية',
                'text_direction' => 'rtl',
                'active' => true,
            ])
            ->assertHasNoFormErrors();

        expect(locale('ar')->is_active)->toBeTrue()
            ->and(Localization::context('web')->supports('ar'))->toBeTrue()
            ->and(Localization::locales()->get('ar')->textDirection->value)->toBe('rtl');
    });

    it('shows validation errors on the fields', function (array $data, array $errors): void {
        Livewire::test(ListLocales::class)
            ->callAction('create', data: [
                'code' => 'fr',
                'name' => 'French',
                'native_name' => 'Français',
                ...$data,
            ])
            ->assertHasFormErrors($errors);

        expect(Locale::query()->count())->toBe(3);
    })->with([
        'missing code' => [['code' => null], ['code' => 'required']],
        'malformed code' => [['code' => 'not a code'], ['code']],
        'duplicate code' => [['code' => 'ka'], ['code']],
        'missing name' => [['name' => ''], ['name' => 'required']],
        'long abbreviation' => [['native_abbreviation' => 'FRAN'], ['native_abbreviation']],
        'lowercase country code' => [['country_code' => 'fr'], ['country_code']],
    ]);

    it('reports a rejected operation instead of failing', function (): void {
        // A locale code equal to a static localized route segment is reserved.
        Localization::routes(fn () => Route::get('/de', fn () => 'page')->name('de-page'));

        Livewire::test(ListLocales::class)
            ->callAction('create', data: [
                'code' => 'de',
                'name' => 'German',
                'native_name' => 'Deutsch',
            ])
            ->assertNotified()
            ->assertActionHalted('create');

        expect(Locale::query()->find('de'))->toBeNull();
    });
});

describe('editing', function (): void {
    beforeEach(function (): void {
        $this->actingAs($this->user(canManageLocalization: true));
    });

    it('fills every field and keeps values that are not changed', function (): void {
        Livewire::test(ListLocales::class)
            ->mountAction(TestAction::make('edit')->table(locale('en')))
            ->assertSchemaStateSet([
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'native_abbreviation' => 'EN',
                'country_code' => 'GB',
                'text_direction' => 'ltr',
            ])
            ->fillForm(['name' => 'English (UK)'])
            ->callMountedAction()
            ->assertHasNoFormErrors()
            ->assertNotified('Locale updated');

        expect(locale('en'))
            ->name->toBe('English (UK)')
            ->native_abbreviation->toBe('EN')
            ->country_code->toBe('GB');
    });

    it('cannot change the code', function (): void {
        Livewire::test(ListLocales::class)
            ->callAction(TestAction::make('edit')->table(locale('ka')), data: ['code' => 'xx', 'name' => 'Kartuli']);

        expect(locale('ka')->name)->toBe('Kartuli')
            ->and(Locale::query()->find('xx'))->toBeNull();
    });
});

describe('state actions', function (): void {
    beforeEach(function (): void {
        $this->actingAs($this->user(canManageLocalization: true));
    });

    it('shows only the actions that apply to a locale', function (): void {
        resolve(DeactivateLocale::class)->execute('ru');

        Livewire::test(ListLocales::class)
            ->assertActionHidden(TestAction::make('make_locale_default')->table(locale('en')))
            ->assertActionHidden(TestAction::make('deactivate_locale')->table(locale('en')))
            ->assertActionHidden(TestAction::make('delete_locale')->table(locale('en')))
            ->assertActionVisible(TestAction::make('make_locale_default')->table(locale('ka')))
            ->assertActionVisible(TestAction::make('deactivate_locale')->table(locale('ka')))
            ->assertActionHidden(TestAction::make('activate_locale')->table(locale('ka')))
            ->assertActionVisible(TestAction::make('activate_locale')->table(locale('ru')))
            ->assertActionVisible(TestAction::make('delete_locale')->table(locale('ru')));
    });

    it('activates, deactivates and deletes locales', function (): void {
        $page = Livewire::test(ListLocales::class)
            ->callAction(TestAction::make('deactivate_locale')->table(locale('ru')))
            ->assertNotified('Locale deactivated');

        expect(locale('ru')->is_active)->toBeFalse();

        $page->callAction(TestAction::make('activate_locale')->table(locale('ru')))
            ->assertNotified('Locale activated');

        expect(locale('ru')->is_active)->toBeTrue();

        $page->callAction(TestAction::make('deactivate_locale')->table(locale('ru')))
            ->callAction(TestAction::make('delete_locale')->table(locale('ru')))
            ->assertNotified('Locale deleted');

        expect(Locale::query()->find('ru'))->toBeNull();
    });

    it('changes the global default', function (): void {
        Livewire::test(ListLocales::class)
            ->callAction(TestAction::make('make_locale_default')->table(locale('ka')))
            ->assertNotified('Default locale updated');

        expect(Localization::locales()->defaultCode())->toBe('ka');
    });

    it('refuses to deactivate a context default', function (): void {
        resolve(UpdateLocaleContext::class)->execute(
            'admin',
            new UpdateLocaleContextData(locales: ['en', 'ka'], default: 'ka'),
        );

        Livewire::test(ListLocales::class)
            ->callAction(TestAction::make('deactivate_locale')->table(locale('ka')))
            ->assertNotified();

        expect(locale('ka')->is_active)->toBeTrue();
    });

});

final class ReorderDeniedPolicy extends LocalePolicy
{
    public function reorder(Authenticatable $user): bool
    {
        return false;
    }

    public function delete(Authenticatable $user, Locale $locale): bool
    {
        return false;
    }
}

it('hides actions the policy denies', function (): void {
    $this->actingAs($this->user(canManageLocalization: true));
    resolve(DeactivateLocale::class)->execute('ru');

    Gate::policy(Locale::class, ReorderDeniedPolicy::class);

    Livewire::test(ListLocales::class)
        ->assertActionHidden(TestAction::make('delete_locale')->table(locale('ru')))
        ->assertActionVisible(TestAction::make('activate_locale')->table(locale('ru')));
});
