<?php

declare(strict_types=1);

namespace Rimba\Who\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Rimba\Who\Contracts\SecurityContextContract;
use Rimba\Who\Contracts\StaffResolverContract;
use Rimba\Who\Enums\SecurityLevel;
use Rimba\Who\Models\UserAuth;
use Rimba\Who\Support\SecurityContext;

final readonly class SecurityContextService implements SecurityContextContract
{
    public function __construct(private StaffResolverContract $staffResolverContract) {}

    public function forUser(Authenticatable $user): SecurityContext
    {
        $r = $this->staffResolverContract->resolve($user);

        $userAuth = UserAuth::query()
            ->where('user_id', $user->getAuthIdentifier())
            ->first();

        $securityLevel = $userAuth
            ? $userAuth->securityLevel()
            : SecurityLevel::Authenticated;

        return new SecurityContext(
            $r['is_staff'],
            $r['is_tmo'],
            $r['is_admin'],
            $securityLevel,
        );
    }
}
