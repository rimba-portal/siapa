<?php

declare(strict_types=1);

namespace Rimba\Who\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Rimba\Who\Models\AuthenticationAttempt;

final class AuthenticationAttemptPolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return (bool) (
            $user->staff?->hasRole(
                config('bites_auth.roles.admin')
            ) ?? false
        );
    }

    public function view(
        Authenticatable $user,
        AuthenticationAttempt $record
    ): bool {
        return $user->getAuthIdentifier() === $record->user_id
            || (
                $user->staff?->hasRole(
                    config('bites_auth.roles.admin')
                ) ?? false
            );
    }
}
