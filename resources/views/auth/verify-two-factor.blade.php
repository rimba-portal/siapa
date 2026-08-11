<x-filament-panels::page.simple>
    <h1 class="text-xl font-semibold">Verify two-factor authentication</h1>
    {{ $this->form }}
    <x-filament::button wire:click="verify" class="w-full">Verify</x-filament::button>
</x-filament-panels::page.simple>
