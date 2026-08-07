<div>
    <x-ui.button class="w-full" variant="secondary" wire:click="logout" wire:loading.attr="disabled" wire:target="logout">
        <span wire:loading.remove wire:target="logout">{{ __('app.logout') }}</span>
        <span wire:loading wire:target="logout">{{ __('app.loading') }}</span>
    </x-ui.button>
</div>
