<x-filament-panels::page.simple>
    <h1 class="text-xl font-semibold">Set up two-factor authentication</h1>
    <p class="text-sm text-gray-500">Add the secret to your authenticator, then enter the current six-digit code.</p>
    {{ $this->form }}
    <x-filament::button wire:click="confirm" class="w-full">Confirm 2FA</x-filament::button>
</x-filament-panels::page.simple>
