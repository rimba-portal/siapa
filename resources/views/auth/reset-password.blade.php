<x-filament-panels::page.simple>
    <form wire:submit="resetPassword">
        {{ $this->form }}

        <x-filament::button type="submit" class="mt-6 w-full"> Reset Password </x-filament::button>
    </form>
</x-filament-panels::page.simple>
