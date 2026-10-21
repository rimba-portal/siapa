<?php

declare(strict_types=1);

namespace Rimba\Who\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Rimba\Who\Contracts\PanelAccessResolverContract;
use Rimba\Who\Enums\PanelId;
use Rimba\Who\Models\UserAuth;

final readonly class PanelAccessService implements PanelAccessResolverContract
{
    public function canAccess(
        Authenticatable $user,
        string $panelId,
    ): bool {
        if ($panelId === PanelId::Lobby->value) {
            return true;
        }

        $userAuth = $this->resolveUserAuth($user);

        if (! $userAuth?->setup_completed) {
            return false;
        }

        $staff = $user->staff ?? null;

        if (! $staff) {
            return false;
        }

        if (in_array(
            $panelId,
            [
                PanelId::Staff->value,
                PanelId::StaffSensitive->value,
            ],
            true,
        )) {
            return true;
        }

        return $staff
            ->getAllPermissions()
            ->contains(
                fn ($permission): bool => $permission->panel === $panelId,
            );
    }

    public function destinationFor(
        Authenticatable $user,
    ): string {
        $userAuth = $this->resolveUserAuth($user);

        if (! $userAuth?->setup_completed) {
            return PanelId::Lobby->value;
        }

        foreach (
            [
                PanelId::Admin,
                PanelId::Team,
                PanelId::Staff,
            ] as $panel
        ) {
            if ($this->canAccess($user, $panel->value)) {
                return $panel->value;
            }
        }

        return PanelId::Lobby->value;
    }

    private function resolveUserAuth(
        Authenticatable $user,
    ): ?UserAuth {
        if (
            method_exists($user, 'relationLoaded')
            && $user->relationLoaded('userAuth')
        ) {
            $userAuth = $user->getRelation('userAuth');

            if ($userAuth instanceof UserAuth) {
                return $userAuth;
            }
        }

        return UserAuth::query()
            ->where(
                'user_id',
                $user->getAuthIdentifier(),
            )
            ->first();
    }
}
