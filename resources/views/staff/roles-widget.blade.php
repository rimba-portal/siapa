<x-filament-widgets::widget>
    <x-filament::section heading="{{ static::$heading }}">
        @php $roles = $this->getRoles(); @endphp

        @if (empty($roles))
            <p class="text-sm text-gray-500 dark:text-gray-400">No roles assigned.</p>
        @else
            <div class="flex flex-wrap gap-2">
                @foreach ($roles as $role)
                    <x-filament::badge color="primary">{{ $role }}</x-filament::badge>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
