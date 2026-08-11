<x-filament-panels::page.simple>
    {{ $this->form }}
    <x-filament::button wire:click="request" class="w-full">Send reset link</x-filament::button>
</x-filament-panels::page.simple>
