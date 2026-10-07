{{-- resources/views/livewire/locale-switcher.blade.php --}}

<div>
    <x-filament::dropdown
        placement="bottom-end"
        maxHeight="36rem"
        teleport
    >
        <x-slot
            name="trigger"
            style="justify-self: center; align-self: center; padding: 0.5rem 0;"
        >
            @if ($showFlags && $currentLocale->countryCode !== null)
                <div
                    style="
                        width: 2rem;
                        height: 2rem;
                        border-radius: 9999px;
                        overflow: hidden;
                        cursor: pointer;
                    "
                >
                    @php
                        try {
                            echo svg(
                                'flag-1x1-' . strtolower($currentLocale->countryCode),
                                '',
                            )->toHtml();

                            $flagFound = true;
                        } catch (Exception) {
                            $flagFound = false;
                        }
                    @endphp

                    @unless ($flagFound)
                        <x-filament::icon
                            icon="heroicon-o-language"
                            style="width: 2rem; height: 2rem;"
                        />
                    @endunless
                </div>
            @else
                <x-filament::icon-button
                    icon="heroicon-o-language"
                    label="Language switcher"
                />
            @endif
        </x-slot>

        <x-filament::dropdown.list
            style="max-height: 20rem; overflow-y: auto;"
        >
            @foreach ($locales as $locale)
                @php
                    $isCurrent = $currentLocale->code === $locale->code;
                @endphp

                <x-filament::dropdown.list.item
                    tag="button"
                    x-on:click="close()"
                    wire:click="switchLocale('{{ $locale->code }}')"
                >
                    <span
                        class="fi-dropdown-list-item-label"
                        style="
                            text-overflow: ellipsis;
                            overflow: hidden;
                            white-space: nowrap;
                            width: 100%;
                            text-align: left;
                            display: flex;
                            justify-content: flex-start;
                            gap: 0.75rem;
                        "
                    >
                        @if ($showFlags)
                            <div
                                style="
                                    width: 1.5rem;
                                    height: 1.5rem;
                                    flex-shrink: 0;
                                "
                            >
                                @php
                                    try {
                                        echo svg(
                                            'flag-4x3-' . strtolower($locale->countryCode ?? 'un'),
                                            '',
                                        )->toHtml();

                                        $itemFlagFound = true;
                                    } catch (Exception) {
                                        $itemFlagFound = false;
                                    }
                                @endphp

                                @unless ($itemFlagFound)
                                    <x-filament::icon
                                        icon="heroicon-o-flag"
                                        style="width: 1.5rem; height: 1.5rem;"
                                    />
                                @endunless
                            </div>

                            <span style="{{ $isCurrent ? 'font-weight: 600;' : '' }}">
                                {{ $locale->nativeName }}
                            </span>
                        @else
                            <span style="{{ $isCurrent ? 'font-weight: 600;' : '' }}">
                                {{ $locale->nativeName }}
                            </span>
                        @endif
                    </span>
                </x-filament::dropdown.list.item>
            @endforeach
        </x-filament::dropdown.list>
    </x-filament::dropdown>
</div>
