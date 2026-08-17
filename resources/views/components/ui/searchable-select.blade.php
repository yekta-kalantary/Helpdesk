@props([
    'name',
    'label' => null,
    'hint' => null,
    'options' => [],
    'value' => null,
    'optionValue' => 'id',
    'optionLabel' => 'name',
    'placeholder' => null,
    'searchPlaceholder' => null,
    'searchModel' => null,
    'selectAction' => null,
    'emptyText' => null,
    'required' => false,
    'disabled' => false,
])

@php
    $id = $attributes->get('id', str_replace(['[', ']', '.'], ['-', '', '-'], $name));
    $listId = $id.'-options';
    $selectedValue = $value === null ? '' : (string) $value;
    $errorId = $id.'-error';
    $normalizedOptions = collect($options)->map(fn ($option) => [
        'value' => data_get($option, $optionValue),
        'label' => (string) data_get($option, $optionLabel),
        'email' => (string) data_get($option, 'email', ''),
        'mobile' => (string) data_get($option, 'mobile', ''),
    ])->values();
@endphp

<div
    x-data="{
        open: false,
        activeIndex: -1,
        options() {
            return Array.from(this.$refs.list?.querySelectorAll('[data-searchable-option]') ?? []);
        },
        show() {
            if (@js((bool) $disabled)) return;
            this.open = true;
            this.$nextTick(() => {
                const options = this.options();
                const selectedIndex = options.findIndex((option) => option.dataset.selected === 'true');
                this.activeIndex = selectedIndex >= 0 ? selectedIndex : (options.length ? 0 : -1);
            });
        },
        move(step) {
            this.open = true;
            const options = this.options();
            if (! options.length) {
                this.activeIndex = -1;
                return;
            }

            this.activeIndex = (this.activeIndex + step + options.length) % options.length;
            options[this.activeIndex]?.scrollIntoView({ block: 'nearest' });
        },
        chooseActive() {
            this.options()[this.activeIndex]?.click();
        },
    }"
    x-on:click.outside="open = false"
    x-on:keydown.escape.window="open = false"
    class="relative min-w-0"
>
    @if($label)
        <label for="{{ $id }}-search" class="mb-1.5 block text-label font-semibold text-field-label">{{ $label }}</label>
    @endif

    <div class="relative">
        <svg
            viewBox="0 0 20 20"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
            class="pointer-events-none absolute end-3 top-1/2 h-4 w-4 -translate-y-1/2 text-text-muted"
            aria-hidden="true"
        >
            <circle cx="9" cy="9" r="5.5" />
            <path stroke-linecap="round" d="m13 13 4 4" />
        </svg>

        <input
            id="{{ $id }}-search"
            type="search"
             role="combobox"
             aria-autocomplete="list"
             aria-controls="{{ $listId }}"
             x-bind:aria-activedescendant="activeIndex >= 0 ? '{{ $listId }}-' + activeIndex : null"
            x-bind:aria-expanded="open.toString()"
            @if($required) aria-required="true" @endif
            @error($name) aria-invalid="true" aria-describedby="{{ $errorId }}" @enderror
            @disabled($disabled)
            @if($searchModel) wire:model.live.debounce.200ms="{{ $searchModel }}" @endif
            placeholder="{{ $searchPlaceholder ?: $placeholder ?: __('app.search') }}"
            autocomplete="off"
            x-on:focus="show()"
            x-on:input="open = true; activeIndex = -1"
            x-on:keydown.arrow-down.prevent="move(1)"
            x-on:keydown.arrow-up.prevent="move(-1)"
            x-on:keydown.enter.prevent="if (open) chooseActive(); else show()"
            x-on:keydown.tab="open = false"
            class="min-h-11 w-full rounded-control border border-input-border bg-input-background py-2 pe-9 ps-10 text-body-sm text-text outline-none transition placeholder:text-text-muted focus:border-focus focus:ring-2 focus:ring-focus/20 disabled:cursor-not-allowed disabled:border-border disabled:bg-surface-muted disabled:text-text-muted disabled:shadow-none disabled:ring-0"
        >

        @if($searchModel)
            <span wire:loading.delay.flex wire:target="{{ $searchModel }}" class="absolute start-3 top-1/2 -translate-y-1/2 items-center" aria-label="در حال جستجو">
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 animate-spin text-text-muted" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" class="opacity-25" />
                    <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
            </span>
        @endif
    </div>

    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="absolute inset-x-0 z-50 mt-1.5 overflow-hidden rounded-surface border border-border bg-surface shadow-subtle"
    >
        <div x-ref="list" id="{{ $listId }}" role="listbox" class="max-h-72 overflow-y-auto overscroll-contain p-1.5">
            @forelse($normalizedOptions as $option)
                @php
                    $optionStringValue = (string) $option['value'];
                    $isSelected = $optionStringValue === $selectedValue;
                @endphp

                 <button
                     type="button"
                     role="option"
                     id="{{ $listId }}-{{ $loop->index }}"
                    data-searchable-option
                    data-selected="{{ $isSelected ? 'true' : 'false' }}"
                    aria-selected="{{ $isSelected ? 'true' : 'false' }}"
                    @if($selectAction) wire:click="{{ $selectAction }}({{ (int) $option['value'] }})" @endif
                    x-on:mouseenter="activeIndex = options().indexOf($el)"
                    x-on:click="open = false"
                    x-bind:class="activeIndex === options().indexOf($el) ? 'bg-surface-muted text-text' : ''"
                    class="flex min-h-11 w-full items-start gap-3 rounded-control px-3 py-2.5 text-right text-body-sm text-text transition hover:bg-surface-muted hover:text-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus"
                >
                    <span class="min-w-0 flex-1">
                        <span class="block truncate {{ $isSelected ? 'font-bold text-text' : 'font-semibold' }}">{{ $option['label'] }}</span>
                        @if($option['email'] || $option['mobile'])
                            <span dir="ltr" class="mt-0.5 flex flex-wrap justify-end gap-x-3 gap-y-0.5 text-caption font-normal text-text-muted">
                                @if($option['email'])<span>{{ $option['email'] }}</span>@endif
                                @if($option['mobile'])<span>{{ $option['mobile'] }}</span>@endif
                            </span>
                        @endif
                    </span>

                    @if($isSelected)
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" class="mt-0.5 h-4 w-4 shrink-0 text-primary" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 10 3 3 7-7" />
                        </svg>
                    @endif
                </button>
            @empty
                <div class="px-3 py-8 text-center text-body-sm text-text-muted">
                    {{ $emptyText ?: __('app.no_records') }}
                </div>
            @endforelse
        </div>
    </div>

    @if($hint)<p class="mt-1.5 text-caption leading-5 text-field-helper">{{ $hint }}</p>@endif
    @error($name)<p id="{{ $errorId }}" class="mt-1.5 text-caption font-medium text-field-error">{{ $message }}</p>@enderror
</div>
