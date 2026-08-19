<x-filament-widgets::widget>
    <x-filament::section heading="Account setup" description="Complete the remaining account setup requirements.">
        <div class="space-y-4">
            <div class="flex flex-wrap gap-2">
                <x-filament::badge :color="$status['profile'] ? 'success' : 'warning'"> Profile </x-filament::badge>

                <x-filament::badge :color="$status['photo'] ? 'success' : 'warning'">
                    Profile picture
                </x-filament::badge>

                <x-filament::badge :color="$status['totp'] ? 'success' : 'warning'"> Recovery TOTP </x-filament::badge>
            </div>

            <x-filament::button tag="a" :href="$onboardingUrl" icon="heroicon-o-arrow-right">
                Continue setup
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
