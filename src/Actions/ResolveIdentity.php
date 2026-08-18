<?php

declare(strict_types=1);

namespace Rimba\Who\Actions;

use Rimba\Who\Models\UserAuth;
use Rimba\Who\Support\IdentityResolutionResult;

final class ResolveIdentity
{
    public function handle(string $identifier): IdentityResolutionResult
    {
        $identifier = strtolower(trim($identifier));
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $model = config('auth.providers.users.model');
            $user = $model::query()->whereRaw('LOWER(email) = ?', [mb_strtolower($identifier)])->first();

            return new IdentityResolutionResult($identifier, $user, $user?->userAuth);
        }

        $userAuth = UserAuth::query()->with('user')->where('auth_identifier', $identifier)->first();

        return new IdentityResolutionResult($identifier, $userAuth?->user, $userAuth);
    }
}
