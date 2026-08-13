@php
// Expect: $staffName, $staff_number, $orgUnitName, $jobPositionName from getViewData()
@endphp

<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
        <x-filament-panels::avatar.user
            size="lg"
            :user="$user"
            loading="lazy" />
        <div class="fi-account-widget-main">
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <!-- <span class="text-lg text-primary">{{ filament()->getUserName($user) }} </span> -->
                <span class="text-lg text-primary">{{ $staffName }}</span>
                <span class="fi-account-widget-user-name"> [ #{{ $staff_number }} ]</span>
            </div>
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">is</span>
                <span class="fi-account-widget-user-name">{{ $jobPositionName }}</span>
                <span class="text-sm text-gray-500 dark:text-gray-400">in</span>
                <span class="fi-account-widget-user-name">{{ $orgUnitName }}</span>
            </div>

        </div>
        <form
            action="{{ filament()->getLogoutUrl() }}"
            method="post"
            class="fi-account-widget-logout-form">
            @csrf

            <x-filament::button
                color="gray"
                :icon="\Filament\Support\Icons\Heroicon::ArrowLeftEndOnRectangle"
                :icon-alias="\Filament\View\PanelsIconAlias::WIDGETS_ACCOUNT_LOGOUT_BUTTON"
                labeled-from="sm"
                tag="button"
                type="submit">
                {{ __('filament-panels::widgets/account-widget.actions.logout.label') }}
            </x-filament::button>
        </form>
    </x-filament::section>
</x-filament-widgets::widget>