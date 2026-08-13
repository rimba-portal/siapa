<?php

declare(strict_types=1);

namespace Rimba\Who\Actions;

use Rimba\Who\Enums\AuthenticationStatus;
use Rimba\Who\Services\IdentityAuthenticatorService;
use Rimba\Who\Services\IdentityResolverService;
use Rimba\Who\Support\AuthenticationResult;

final readonly class AuthenticateUser
{
    public function __construct(private ResolveIdentity $resolveIdentity, private IdentityResolverService $identityResolverService, private IdentityAuthenticatorService $identityAuthenticatorService, private RecordAuthenticationAttempt $recordAuthenticationAttempt, private LoginUser $loginUser) {}

    public function handle(string $identifier, string $password, bool $remember = false): AuthenticationResult
    {
        $identityResolutionResult = $this->resolveIdentity->handle($identifier);
        if ($identityResolutionResult->exists()) {
            $result = $this->identityAuthenticatorService->existing((string) $identityResolutionResult->provider(), $identifier, $password);
        } else {
            $external = $this->identityResolverService->find($identifier);
            $result = $external ? $this->identityAuthenticatorService->external($external, $password) : new AuthenticationResult(AuthenticationStatus::NotFound, 'identity', reason: 'user_not_found');
        }

        $this->recordAuthenticationAttempt->handle($identifier, $result);
        if ($result->succeeded() && $result->user) {
            $this->loginUser->handle($result->user, $remember);
        }

        return $result;
    }
}
