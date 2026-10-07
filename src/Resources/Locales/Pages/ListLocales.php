<?php

declare(strict_types=1);

namespace Tipi\Localization\Filament\Resources\Locales\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Tipi\Localization\Filament\Actions\CreateLocaleAction;
use Tipi\Localization\Filament\Resources\Locales\LocaleResource;

final class ListLocales extends ListRecords
{
    protected static string $resource = LocaleResource::class;

    public function getHeaderActions(): array
    {
        return [
            CreateLocaleAction::make(),
        ];
    }

    public function getTabs(): array
{
    return [
        'all' => Tab::make(),
        'active' => Tab::make()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true)),
        'inactive' => Tab::make()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false)),
    ];
}
}