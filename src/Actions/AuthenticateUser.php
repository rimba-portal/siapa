<?php

declare(strict_types=1);

namespace Rimba\Who\Actions;

use Illuminate\Support\Facades\Auth;
use Rimba\Who\Contracts\AuthOrchestratorContract;
use Rimba\Who\Models\UserAuth;
use Rimba\Who\Services\UserRoleResolver;
use Rimba\Who\Support\AuthenticationResult;

final readonly class AuthenticateUser
{
    public function __construct(private AuthOrchestratorContract $authOrchestratorContract) {}

    public function handle(string $identifier, string $password, bool $remember = false): AuthenticationResult
    {
        $authenticationResult = $this->authOrchestratorContract->authenticate($identifier, $password);

        if ($authenticationResult->succeeded() && $authenticationResult->user) {
            Auth::login($authenticationResult->user, $remember);
            app(UserRoleResolver::class)
                ->sync($authenticationResult->user);
            session()->regenerate();
            UserAuth::query()->updateOrCreate(
                ['user_id' => $authenticationResult->user->getAuthIdentifier()],
                [
                    'auth_provider' => $authenticationResult->provider,
                    'auth_identifier' => $identifier,
                    'last_login_at' => now(),
                ],
            );
        }

        return $authenticationResult;
    }
}
