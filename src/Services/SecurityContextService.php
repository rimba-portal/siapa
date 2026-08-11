<?php

declare(strict_types=1);

namespace Rimba\Who\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Rimba\Who\Contracts\SecurityContextContract;
use Rimba\Who\Contracts\StaffResolverContract;
use Rimba\Who\Models\UserAuth;
use Rimba\Who\Support\SecurityContext;

final readonly class SecurityContextService implements SecurityContextContract
{
    public function __construct(private StaffResolverContract $staffResolverContract) {}

    public function forUser(Authenticatable $user): SecurityContext
    {
        $roles = $this->staffResolverContract->resolve($user);
        $userAuth = UserAuth::query()->firstOrCreate(['user_id' => $user->getAuthIdentifier()]);

        return new SecurityContext(
            isStaff: $roles['is_staff'],
            isTmo: $roles['is_tmo'],
            isAdmin: $roles['is_admin'],
            level: $userAuth->securityLevel(),
        );
    }
}
