<?php

declare(strict_types=1);

namespace Rimba\Who\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Rimba\Who\Contracts\StaffResolverContract;

final class StaffResolverService implements StaffResolverContract
{
    public function resolve(Authenticatable $user): array
    {
        $staff = $user->staff ?? null;

        return [
            'is_staff' => $staff !== null,
            'is_tmo' => (bool) ($staff?->hasRole(config('siapa.roles.team_planner')) ?? false),
            'is_admin' => (bool) ($staff?->hasRole(config('siapa.roles.admin')) ?? false),
        ];
    }
}
