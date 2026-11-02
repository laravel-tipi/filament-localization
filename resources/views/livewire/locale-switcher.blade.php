<div class="fi-tipi-locale-switcher">
    @if ($locales->count() > 1)
        <x-filament::dropdown
            placement="bottom-end"
            max-height="36rem"
            teleport
        >
            <x-slot name="trigger">
                @if ($currentFlag !== null)
                    <button
                        type="button"
                        title="{{ __('tipi-filament-localization::switcher.label') }}"
                        aria-label="{{ __('tipi-filament-localization::switcher.label') }}"
                        style="display: flex; width: 2rem; height: 2rem; border-radius: 9999px; overflow: hidden; cursor: pointer;"
                    >
                        {!! $currentFlag !!}
                    </button>
                @else
                    <x-filament::icon-button
                        icon="heroicon-o-language"
                        :label="__('tipi-filament-localization::switcher.label')"
                        color="gray"
                    />
                @endif
            </x-slot>

            <x-filament::dropdown.list>
                @foreach ($locales as $entry)
                    @php($locale = $entry['locale'])

                    <x-filament::dropdown.list.item
                        tag="button"
                        :color="$entry['current'] ? 'primary' : 'gray'"
                        :aria-current="$entry['current'] ? 'true' : null"
                        :lang="$locale->code"
                        :dir="$locale->textDirection->value"
                        x-on:click="close()"
                        wire:click="switchLocale({{ \Illuminate\Support\Js::from($locale->code) }})"
                        wire:key="tipi-locale-{{ $locale->code }}"
                    >
                        <span style="display: flex; align-items: center; gap: 0.75rem;">
                            @if ($showFlags)
                                <span style="display: flex; width: 1.5rem; flex-shrink: 0;">
                                    @if ($entry['flag'] !== null)
                                        {!! $entry['flag'] !!}
                                    @else
                                        <x-filament::icon icon="heroicon-o-flag" style="width: 1.5rem; height: 1.5rem;" />
                                    @endif
                                </span>
                            @endif

                            <span @style(['font-weight: 600' => $entry['current']])>
                                {{ $locale->nativeName }}
                            </span>
                        </span>
                    </x-filament::dropdown.list.item>
                @endforeach
            </x-filament::dropdown.list>
        </x-filament::dropdown>
    @endif
</div>
