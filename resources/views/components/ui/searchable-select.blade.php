@props([
    'name',
    'label' => null,
    'hint' => null,
    'options' => [],
    'value' => null,
    'optionValue' => 'id',
    'optionLabel' => 'name',
    'placeholder' => '—',
    'searchPlaceholder' => null,
    'searchModel' => null,
    'searchValue' => '',
    'emptyText' => null,
    'required' => false,
    'disabled' => false,
])

@php
    $id = $attributes->get('id', str_replace(['[', ']', '.'], ['-', '', '-'], $name));
    $listId = $id.'-options';
    $wireModelAttributes = $attributes->whereStartsWith('wire:model');
    $normalizedOptions = collect($options)->map(fn ($option) => [
        'value' => data_get($option, $optionValue),
        'label' => (string) data_get($option, $optionLabel),
    ])->values();
    $selectedValue = $value === null ? '' : (string) $value;
    $selectedLabel = (string) ($normalizedOptions->first(
        fn (array $option) => (string) $option['value'] === $selectedValue,
    )['label'] ?? '');
@endphp

<div
    x-data="{
        open: false,
        query: @js((string) $searchValue),
        selected: @js($selectedValue),
        selectedLabel: @js($selectedLabel),
        activeIndex: -1,
        remoteSearch: @js((bool) $searchModel),
        disabled: @js((bool) $disabled),
        normalize(value) {
            return String(value ?? '').trim().toLocaleLowerCase();
        },
        matches(label) {
            if (this.remoteSearch || this.query.trim() === '') {
                return true;
            }

            return this.normalize(label).includes(this.normalize(this.query));
        },
        availableOptions() {
            return Array.from(this.$refs.list.querySelectorAll('[data-searchable-option]'))
                .filter((option) => this.matches(option.dataset.label));
        },
        visibleCount() {
            void this.query;
            return this.availableOptions().length;
        },
        openMenu() {
            if (this.disabled) {
                return;
            }

            this.open = ! this.open;

            if (! this.open) {
                return;
            }

            this.$nextTick(() => {
                const options = this.availableOptions();
                const selectedIndex = options.findIndex((option) => option.dataset.value === this.selected);
                this.activeIndex = selectedIndex >= 0 ? selectedIndex : (options.length ? 0 : -1);
                this.$refs.search?.focus();
                this.scrollActiveIntoView();
            });
        },
        move(step) {
            const options = this.availableOptions();

            if (! options.length) {
                this.activeIndex = -1;
                return;
            }

            this.activeIndex = (this.activeIndex + step + options.length) % options.length;
            this.scrollActiveIntoView();
        },
        scrollActiveIntoView() {
            this.$nextTick(() => {
                this.availableOptions()[this.activeIndex]?.scrollIntoView({ block: 'nearest' });
            });
        },
        choose(value, label) {
            this.selected = String(value);
            this.selectedLabel = label;
            this.$refs.value.value = value;
            this.$refs.value.dispatchEvent(new Event('input', { bubbles: true }));
            this.$refs.value.dispatchEvent(new Event('change', { bubbles: true }));
            this.open = false;
            this.activeIndex = -1;
            this.$refs.trigger.focus();
        },
        chooseActive() {
            const option = this.availableOptions()[this.activeIndex];
            option?.click();
        },
    }"
    x-on:click.outside="open = false"
    x-on:keydown.escape.window="if (open) { open = false; $refs.trigger.focus(); }"
    class="relative min-w-0"
>
    @if($label)
        <label for="{{ $id }}-trigger" class="mb-1.5 block text-sm font-semibold text-slate-700">{{ $label }}</label>
    @endif

    <input
        x-ref="value"
        type="hidden"
        id="{{ $id }}"
        name="{{ $name }}"
        value="{{ $selectedValue }}"
        {{ $wireModelAttributes }}
    >

    <button
        x-ref="trigger"
        id="{{ $id }}-trigger"
        type="button"
        role="combobox"
        aria-haspopup="listbox"
        aria-controls="{{ $listId }}"
        x-bind:aria-expanded="open.toString()"
        @if($required) aria-required="true" @endif
        @disabled($disabled)
        x-on:click="openMenu()"
        x-on:keydown.arrow-down.prevent="if (! open) openMenu(); else move(1)"
        x-on:keydown.arrow-up.prevent="if (! open) openMenu(); else move(-1)"
        x-on:keydown.enter.prevent="if (open) chooseActive(); else openMenu()"
        class="flex min-h-10 w-full items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-right text-sm text-slate-950 outline-none transition hover:border-slate-400 focus:border-slate-500 focus:ring-2 focus:ring-slate-200 disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-500 disabled:shadow-none disabled:ring-0"
    >
        <span
            class="min-w-0 flex-1 truncate"
            x-bind:class="selectedLabel ? 'text-slate-950' : 'text-slate-400'"
            x-text="selectedLabel || @js($placeholder)"
        ></span>

        <svg
            viewBox="0 0 20 20"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            class="h-4 w-4 shrink-0 text-slate-400 transition-transform"
            x-bind:class="open ? 'rotate-180' : ''"
            aria-hidden="true"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="m6 8 4 4 4-4" />
        </svg>
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="absolute inset-x-0 z-50 mt-1.5 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl shadow-slate-950/10"
    >
        <div class="border-b border-slate-100 p-2">
            <div class="relative">
                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" aria-hidden="true">
                    <circle cx="9" cy="9" r="5.5" />
                    <path stroke-linecap="round" d="m13 13 4 4" />
                </svg>

                @if($searchModel)
                    <input
                        x-ref="search"
                        type="search"
                        value="{{ $searchValue }}"
                        x-model="query"
                        wire:model.live.debounce.300ms="{{ $searchModel }}"
                        placeholder="{{ $searchPlaceholder ?: __('app.search') }}"
                        autocomplete="off"
                        x-on:keydown.arrow-down.prevent="move(1)"
                        x-on:keydown.arrow-up.prevent="move(-1)"
                        x-on:keydown.enter.prevent="chooseActive()"
                        class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pe-9 ps-9 text-sm text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:bg-white focus:ring-2 focus:ring-slate-100"
                    >

                    <span wire:loading.flex wire:target="{{ $searchModel }}" class="absolute left-3 top-1/2 -translate-y-1/2 items-center" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 animate-spin text-slate-400">
                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" class="opacity-25" />
                            <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </span>
                @else
                    <input
                        x-ref="search"
                        type="search"
                        x-model.debounce.100ms="query"
                        placeholder="{{ $searchPlaceholder ?: __('app.search') }}"
                        autocomplete="off"
                        x-on:input="activeIndex = availableOptions().length ? 0 : -1"
                        x-on:keydown.arrow-down.prevent="move(1)"
                        x-on:keydown.arrow-up.prevent="move(-1)"
                        x-on:keydown.enter.prevent="chooseActive()"
                        class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pe-9 ps-3 text-sm text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:bg-white focus:ring-2 focus:ring-slate-100"
                    >
                @endif
            </div>
        </div>

        <div x-ref="list" id="{{ $listId }}" role="listbox" class="max-h-64 overflow-y-auto overscroll-contain p-1.5">
            @foreach($normalizedOptions as $index => $option)
                @php
                    $optionStringValue = (string) $option['value'];
                    $optionLabelText = $option['label'];
                @endphp

                <button
                    type="button"
                    role="option"
                    data-searchable-option
                    data-value="{{ $optionStringValue }}"
                    data-label="{{ $optionLabelText }}"
                    x-show="matches(@js($optionLabelText))"
                    x-bind:aria-selected="(selected === @js($optionStringValue)).toString()"
                    x-bind:class="{
                        'bg-slate-100 text-slate-950': activeIndex === availableOptions().indexOf($el),
                        'font-semibold': selected === @js($optionStringValue),
                    }"
                    x-on:mouseenter="activeIndex = availableOptions().indexOf($el)"
                    x-on:click="choose(@js($optionStringValue), @js($optionLabelText))"
                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-right text-sm text-slate-700 transition hover:bg-slate-100 hover:text-slate-950"
                >
                    <span class="min-w-0 flex-1 truncate">{{ $optionLabelText }}</span>
                    <svg x-show="selected === @js($optionStringValue)" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4 shrink-0 text-slate-700" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 10 3 3 7-7" />
                    </svg>
                </button>
            @endforeach

            <div x-show="visibleCount() === 0" class="px-3 py-8 text-center text-sm text-slate-500">
                {{ $emptyText ?: __('app.no_records') }}
            </div>
        </div>
    </div>

    @if($hint)<p class="mt-1.5 text-xs leading-5 text-slate-500">{{ $hint }}</p>@endif
    @error($name)<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
</div>
