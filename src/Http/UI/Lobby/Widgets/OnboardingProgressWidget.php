<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Lobby\Widgets;

use Filament\Widgets\Widget;
use Rimba\Who\Models\UserAuth;

class OnboardingProgressWidget extends Widget
{
    protected string $view =
        'bites::lobby.onboarding-progress-widget';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [
                'status' => [
                    'profile' => false,
                    'photo' => false,
                    'totp' => false,
                ],
                'onboardingUrl' => '#',
            ];
        }

        $userAuth = UserAuth::query()
            ->where('user_id', $user->getAuthIdentifier())
            ->first();

        return [
            'status' => [
                'profile' => $this->hasProfile($user),
                'photo' => $this->hasPhoto($user),
                'totp' => $userAuth?->hasTotp() ?? false,
            ],

            'onboardingUrl' => route('siapa.onboarding'),
        ];
    }

    private function hasProfile($user): bool
    {
        return filled($user->name)
            && filled($user->email);
    }

    private function hasPhoto($user): bool
    {
        return filled(
            $user->personAttributes()
                ->where('key', 'photo')
                ->value('value')
        );
    }
}
