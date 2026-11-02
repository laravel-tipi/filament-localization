<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Resources\Locales\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Tipi\Localization\Actions\ReorderLocales;
use Tipi\Localization\Filament\Actions\CreateLocaleAction;
use Tipi\Localization\Filament\Resources\Locales\LocaleResource;

final class ListLocales extends ListRecords
{
    protected static string $resource = LocaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateLocaleAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('tipi-filament-localization::locales.tabs.all')),
            'active' => Tab::make(__('tipi-filament-localization::locales.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('is_active', true)),
            'inactive' => Tab::make(__('tipi-filament-localization::locales.tabs.inactive'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('is_active', false)),
        ];
    }

    /**
     * The display order is owned by the core package: write it through
     * ReorderLocales instead of updating the position column directly.
     * Codes that are not listed (for example on a filtered tab) keep their
     * relative order after the listed ones.
     *
     * @param  array<int|string>  $order
     */
    public function reorderTable(array $order, int|string|null $draggedRecordKey = null): void
    {
        if (! $this->getTable()->isReorderable()) {
            return;
        }

        resolve(ReorderLocales::class)->execute(array_map(strval(...), array_values($order)));

        $this->flushCachedTableRecords();
    }
}
