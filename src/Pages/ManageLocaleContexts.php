<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Tipi\Localization\Actions\CreateLocaleContext;
use Tipi\Localization\Actions\DeleteLocaleContext;
use Tipi\Localization\Actions\UpdateLocaleContext;
use Tipi\Localization\Config\LocalizationConfig;
use Tipi\Localization\Contexts\LocaleContextDefinition;
use Tipi\Localization\Data\CreateLocaleContextData;
use Tipi\Localization\Data\UpdateLocaleContextData;
use Tipi\Localization\Facades\Localization;
use Tipi\Localization\Filament\FilamentLocalizationPlugin;
use Tipi\Localization\Filament\Support\LocalizationOperation;
use Tipi\Localization\Models\LocaleContext;
use Tipi\Localization\Rules\LocaleContextRules;
use Tipi\Support\Locale;
use UnitEnum;

/**
 * Manages database-backed locale contexts through the core actions.
 *
 * Contexts declared in config always exist, even before a row is stored,
 * so the table lists the core definitions rather than database rows.
 * Authorized by the core LocaleContextPolicy.
 */
final class ManageLocaleContexts extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $slug = 'locale-contexts';

    public static function canAccess(): bool
    {
        return Gate::allows('viewAny', self::modelClass());
    }

    public static function getNavigationLabel(): string
    {
        return __('tipi-filament-localization::contexts.navigation.label');
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return FilamentLocalizationPlugin::currentNavigationGroup();
    }

    public function getTitle(): string
    {
        return __('tipi-filament-localization::contexts.title');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedTable::make(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label(__('tipi-filament-localization::contexts.actions.create.label'))
                ->modalHeading(__('tipi-filament-localization::contexts.actions.create.label'))
                ->successNotificationTitle(__('tipi-filament-localization::contexts.actions.create.success'))
                ->icon(Heroicon::OutlinedPlus)
                ->authorize(fn (): bool => Gate::allows('create', self::modelClass()))
                ->schema([
                    TextInput::make('name')
                        ->label(__('tipi-filament-localization::contexts.fields.name'))
                        ->helperText(__('tipi-filament-localization::contexts.helpers.name'))
                        ->required()
                        ->rules(LocaleContextRules::create()['name']),
                    ...self::localeFields(),
                ])
                ->fillForm(['all_locales' => true])
                ->action(fn (Action $action, array $data) => LocalizationOperation::run(
                    $action,
                    function () use ($data): void {
                        [$locales, $default] = self::localeSelection($data);

                        resolve(CreateLocaleContext::class)->execute(
                            new CreateLocaleContextData(name: $data['name'], locales: $locales, default: $default),
                        );

                        $this->flushCachedTableRecords();
                    },
                )),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): array => Localization::contexts()->definitions()
                ->map(fn (LocaleContextDefinition $definition): array => [
                    'name' => $definition->name,
                    'locales' => $definition->locales,
                    'default' => $definition->default,
                    'required' => $definition->required,
                    'persisted' => $definition->persisted,
                ])
                ->all())
            ->columns([
                TextColumn::make('name')
                    ->label(__('tipi-filament-localization::contexts.fields.name'))
                    ->weight('medium'),
                TextColumn::make('locales')
                    ->label(__('tipi-filament-localization::contexts.fields.locales'))
                    ->state(fn (array $record): array => $record['locales'] ?? [__('tipi-filament-localization::contexts.values.all_locales')])
                    ->badge()
                    ->color(fn (array $record): string => $record['locales'] === null ? 'success' : 'gray'),
                TextColumn::make('default')
                    ->label(__('tipi-filament-localization::contexts.fields.default'))
                    ->state(fn (array $record): string => $record['default'] ?? __(
                        'tipi-filament-localization::contexts.values.global_default',
                        ['locale' => Localization::locales()->defaultCode()],
                    )),
                TextColumn::make('status')
                    ->label(__('tipi-filament-localization::contexts.fields.status'))
                    ->state(fn (array $record): array => array_values(array_filter([
                        $record['name'] === resolve(LocalizationConfig::class)->defaultContext ? 'default_context' : null,
                        $record['required'] ? 'declared' : null,
                        $record['persisted'] ? 'stored' : null,
                    ])))
                    ->formatStateUsing(fn (string $state): string => __('tipi-filament-localization::contexts.statuses.'.$state))
                    ->badge()
                    ->color(fn (string $state): string => $state === 'default_context' ? 'success' : 'gray'),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label(__('tipi-filament-localization::contexts.actions.edit.label'))
                    ->successNotificationTitle(__('tipi-filament-localization::contexts.actions.edit.success'))
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->authorize(fn (array $record): bool => Gate::allows('update', self::model($record['name'])))
                    ->schema(self::localeFields())
                    ->fillForm(fn (array $record): array => [
                        'all_locales' => $record['locales'] === null,
                        'locales' => $record['locales'] ?? [],
                        'default' => $record['default'],
                    ])
                    ->action(fn (Action $action, array $record, array $data) => LocalizationOperation::run(
                        $action,
                        function () use ($record, $data): void {
                            [$locales, $default] = self::localeSelection($data);

                            resolve(UpdateLocaleContext::class)->execute(
                                $record['name'],
                                new UpdateLocaleContextData(locales: $locales, default: $default),
                            );

                            $this->flushCachedTableRecords();
                        },
                    )),
                Action::make('delete')
                    ->label(__('tipi-filament-localization::contexts.actions.delete.label'))
                    ->successNotificationTitle(__('tipi-filament-localization::contexts.actions.delete.success'))
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (array $record): bool => ! $record['required'])
                    ->authorize(fn (array $record): bool => Gate::allows('delete', self::model($record['name'])))
                    ->action(fn (Action $action, array $record) => LocalizationOperation::run(
                        $action,
                        function () use ($record): void {
                            resolve(DeleteLocaleContext::class)->execute($record['name']);

                            $this->flushCachedTableRecords();
                        },
                    )),
            ]);
    }

    /**
     * @return list<Component>
     */
    private static function localeFields(): array
    {
        $rules = LocaleContextRules::update();

        return [
            Toggle::make('all_locales')
                ->label(__('tipi-filament-localization::contexts.fields.all_locales'))
                ->helperText(__('tipi-filament-localization::contexts.helpers.all_locales'))
                ->live(),
            Select::make('locales')
                ->label(__('tipi-filament-localization::contexts.fields.locales'))
                ->helperText(__('tipi-filament-localization::contexts.helpers.locales'))
                ->multiple()
                ->options(fn (): array => self::localeOptions(Localization::locales()->all()))
                ->default(fn (): array => [Localization::locales()->defaultCode()])
                ->required(fn (Get $get): bool => ! $get('all_locales'))
                ->hidden(fn (Get $get): bool => (bool) $get('all_locales'))
                ->rules($rules['locales'])
                ->nestedRecursiveRules($rules['locales.*'])
                ->live(),
            Select::make('default')
                ->label(__('tipi-filament-localization::contexts.fields.default'))
                ->helperText(__('tipi-filament-localization::contexts.helpers.default'))
                ->placeholder(fn (): string => __(
                    'tipi-filament-localization::contexts.values.global_default',
                    ['locale' => Localization::locales()->defaultCode()],
                ))
                ->options(function (Get $get): array {
                    $locales = Localization::locales()->active();

                    if (! $get('all_locales')) {
                        $locales = $locales->only([...(array) $get('locales'), Localization::locales()->defaultCode()]);
                    }

                    return self::localeOptions($locales);
                })
                ->rules($rules['default']),
        ];
    }

    /**
     * Context locales (null = every active locale) and default. The global
     * default must belong to every context, so it is always included.
     *
     * @param  array<string, mixed>  $data
     * @return array{0: list<string>|null, 1: string|null}
     */
    private static function localeSelection(array $data): array
    {
        $default = filled($data['default'] ?? null) ? (string) $data['default'] : null;

        if ($data['all_locales'] ?? false) {
            return [null, $default];
        }

        $locales = array_values(array_unique([
            Localization::locales()->defaultCode(),
            ...array_map(strval(...), (array) ($data['locales'] ?? [])),
        ]));

        return [$locales, $default];
    }

    /**
     * @param  Collection<string, Locale>  $locales
     * @return array<string, string>
     */
    private static function localeOptions(Collection $locales): array
    {
        return $locales->map(fn (Locale $locale): string => $locale->isActive()
            ? "{$locale->nativeName} ({$locale->code})"
            : "{$locale->nativeName} ({$locale->code}) – ".__('tipi-filament-localization::locales.statuses.inactive'),
        )->all();
    }

    /**
     * @return class-string<LocaleContext>
     */
    private static function modelClass(): string
    {
        return resolve(LocalizationConfig::class)->contextModel;
    }

    /**
     * The stored row, or an unsaved instance for a declared context that
     * has no row yet, so policies always receive a model.
     */
    private static function model(string $name): LocaleContext
    {
        $class = self::modelClass();

        return $class::query()->find($name) ?? (new $class)->forceFill(['name' => $name]);
    }
}
