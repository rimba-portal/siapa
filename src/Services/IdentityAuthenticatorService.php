<?php

declare(strict_types=1);

namespace Rimba\Who\Services;

use Rimba\Who\Contracts\IdentityAuthenticatorContract;
use Rimba\Who\Enums\AuthenticationStatus;
use Rimba\Who\Support\AuthenticationResult;
use Rimba\Who\Support\ExternalIdentity;

final readonly class IdentityAuthenticatorService
{
    public function __construct(private iterable $authenticators) {}

    public function existing(string $provider, string $identifier, string $password): AuthenticationResult
    {
        $a = $this->find($provider);

        return $a?->authenticateExisting($identifier, $password) ?? new AuthenticationResult(AuthenticationStatus::Failed, $provider, reason: 'provider_not_registered');
    }

    public function external(ExternalIdentity $identity, string $password): AuthenticationResult
    {
        $a = $this->find($identity->provider);

        return $a?->authenticateExternal($identity, $password) ?? new AuthenticationResult(AuthenticationStatus::Failed, $identity->provider, reason: 'provider_not_registered');
    }

    private function find(string $provider): ?IdentityAuthenticatorContract
    {
        foreach ($this->authenticators as $authenticator) {
            if ($authenticator->name() === $provider) {
                return $authenticator;
            }
        }

return null;
    }
}
