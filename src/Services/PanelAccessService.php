<?php

declare(strict_types=1);

namespace Rimba\Who\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Rimba\Who\Contracts\PanelAccessResolverContract;
use Rimba\Who\Contracts\SecurityContextContract;
use Rimba\Who\Enums\PanelId;
use Rimba\Who\Enums\SecurityLevel;

final readonly class PanelAccessService implements PanelAccessResolverContract
{
    public function __construct(private SecurityContextContract $securityContextContract) {}

    public function canAccess(Authenticatable $user, string $panelId): bool
    {
        $securityContext = $this->securityContextContract->forUser($user);

        return match ($panelId) {
            PanelId::Lobby->value => true,
            PanelId::Staff->value => $securityContext->isStaff,
            PanelId::StaffSensitive->value => $securityContext->isStaff && $securityContext->level === SecurityLevel::FaceVerified,
            PanelId::Team->value => $securityContext->isStaff && $securityContext->isTmo && $securityContext->level === SecurityLevel::FaceVerified,
            PanelId::Admin->value => $securityContext->isStaff && $securityContext->isAdmin && $securityContext->level === SecurityLevel::FaceVerified,
            default => false
        };
    }

    public function destinationFor(Authenticatable $user): string
    {
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
}
