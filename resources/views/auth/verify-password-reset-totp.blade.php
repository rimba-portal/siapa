<x-filament-panels::page.simple>
    <form wire:submit="verify">
        {{ $this->form }}

        <x-filament::button type="submit" class="mt-6 w-full"> Verify </x-filament::button>
    </form>
</x-filament-panels::page.simple>
