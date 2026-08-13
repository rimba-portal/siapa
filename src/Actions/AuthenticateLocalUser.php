<?php

declare(strict_types=1);

namespace Rimba\Who\Actions;

use Illuminate\Support\Facades\Hash;
use Rimba\Who\Enums\AuthenticationStatus;
use Rimba\Who\Models\UserAuth;
use Rimba\Who\Support\AuthenticationResult;

final class AuthenticateLocalUser
{
    public function handle(string $identifier, string $password): AuthenticationResult
    {
        $auth = UserAuth::query()->with('user')->where('auth_provider', 'local')->where('auth_identifier', $identifier)->first();
        if (! $auth && filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $model = config('auth.providers.users.model');
            $user = $model::query()->whereRaw('LOWER(email) = ?', [mb_strtolower($identifier)])->first();
            $auth = $user?->userAuth;
        }

        $user = $auth?->user;
        if (! $user) {
            return new AuthenticationResult(AuthenticationStatus::NotFound, 'local', reason: 'user_not_found');
        }

        if (! Hash::check($password, $user->getAuthPassword())) {
            return new AuthenticationResult(AuthenticationStatus::Failed, 'local', $user, 'invalid_credentials');
        }

        return new AuthenticationResult(AuthenticationStatus::Success, 'local', $user);
    }
}
