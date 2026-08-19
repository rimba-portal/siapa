<?php

declare(strict_types=1);

namespace Rimba\Who\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Rimba\Who\Contracts\PanelAccessResolverContract;
use Rimba\Who\Contracts\SecurityContextContract;
use Rimba\Who\Enums\PanelId;
use Rimba\Who\Enums\SecurityLevel;
use Rimba\Who\Models\UserAuth;

final readonly class PanelAccessService implements PanelAccessResolverContract
{
    public function __construct(
        private SecurityContextContract $securityContextContract,
    ) {}

    public function canAccess(Authenticatable $user, string $panelId): bool
    {
        /*
         * Every authenticated user can enter Lobby.
         *
         * An incomplete user must remain able to access Lobby so they can
         * complete profile, photo, and TOTP setup.
         */
        if ($panelId === PanelId::Lobby->value) {
            return true;
        }

        $userAuth = $this->resolveUserAuth($user);

        /*
         * No authentication record or incomplete onboarding means that
         * Staff, Team, and Admin panels remain unavailable.
         */
        if (! $userAuth?->setup_completed) {
            return false;
        }

        $securityContext = $this->securityContextContract
            ->forUser($user);

        return match ($panelId) {
            PanelId::Staff->value => $securityContext->isStaff,
            PanelId::StaffSensitive->value => $securityContext->isStaff
                && $securityContext->level === SecurityLevel::FaceVerified,
            PanelId::Team->value => $securityContext->isStaff
                && $securityContext->isTmo
                && $securityContext->level === SecurityLevel::FaceVerified,
            PanelId::Admin->value => $securityContext->isStaff
                && $securityContext->isAdmin
                && $securityContext->level === SecurityLevel::FaceVerified,
            default => false,
        };
    }

    public function destinationFor(Authenticatable $user): string
    {
        $userAuth = $this->resolveUserAuth($user);

        /*
         * A newly registered or incomplete user always lands in Lobby,
         * even if an existing Staff record has already been linked.
         */
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

    private function resolveUserAuth(Authenticatable $user): ?UserAuth
    {
        /*
         * Prefer an already loaded relationship, but do not require
         * the application User model to expose it.
         */
        if (method_exists($user, 'relationLoaded') && $user->relationLoaded('userAuth')) {
            $userAuth = $user->getRelation('userAuth');

            if ($userAuth instanceof UserAuth) {
                return $userAuth;
            }
        }

        return UserAuth::query()
            ->where('user_id', $user->getAuthIdentifier())
            ->first();
    }
}
