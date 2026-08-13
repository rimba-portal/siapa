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
        $c = $this->securityContextContract->forUser($user);

        return match ($panelId) {
            PanelId::Lobby->value => true,PanelId::Staff->value => $c->isStaff,PanelId::StaffSensitive->value => $c->isStaff && $c->level === SecurityLevel::FaceVerified,PanelId::Team->value => $c->isStaff && $c->isTmo && $c->level === SecurityLevel::FaceVerified,PanelId::Admin->value => $c->isStaff && $c->isAdmin && $c->level === SecurityLevel::FaceVerified,default => false
        };
    }

    public function destinationFor(Authenticatable $user): string
    {
        foreach ([PanelId::Admin, PanelId::Team, PanelId::StaffSensitive, PanelId::Staff] as $p) {
            if ($this->canAccess($user, $p->value)) {
                return $p->value;
            }
        }

return PanelId::Lobby->value;
    }
}
