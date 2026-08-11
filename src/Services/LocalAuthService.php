<?php

declare(strict_types=1);

namespace Rimba\Who\Services;

use Illuminate\Support\Facades\Hash;
use Rimba\Who\Contracts\AuthProviderContract;
use Rimba\Who\Enums\AuthenticationStatus;
use Rimba\Who\Support\AuthenticationResult;

final class LocalAuthService implements AuthProviderContract
{
    public function name(): string
    {
        return 'local';
    }

    public function authenticate(string $identifier, string $password): AuthenticationResult
    {
        $model = config('auth.providers.users.model');
        $user = $model::query()
            ->where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        if (! $user) {
            return new AuthenticationResult(AuthenticationStatus::NotFound, $this->name());
        }

        if (! Hash::check($password, $user->getAuthPassword())) {
            return new AuthenticationResult(AuthenticationStatus::Failed, $this->name(), $user, 'invalid_credentials');
        }

        return new AuthenticationResult(AuthenticationStatus::Success, $this->name(), $user);
    }
}
