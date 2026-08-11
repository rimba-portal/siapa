<x-filament-panels::page.simple>
    <h1 class="text-xl font-semibold">Face verification</h1>
    <form wire:submit="faceMatched">
        <x-siapa::face-auth name="face" />
        <x-filament::button type="submit" class="mt-4 w-full">Continue after match</x-filament::button>
    </form>
</x-filament-panels::page.simple>
