<x-filament-panels::page>
    <form wire:submit="save">{{ $this->form }}</form>
    <x-filament-actions::modals />
    {{-- Page content --}}
</x-filament-panels::page>
