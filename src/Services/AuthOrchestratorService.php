<?php

declare(strict_types=1);

namespace Rimba\Who\Services;

use Rimba\Who\Contracts\AuthOrchestratorContract;
use Rimba\Who\Contracts\AuthProviderContract;
use Rimba\Who\Enums\AuthenticationStatus;
use Rimba\Who\Support\AuthenticationResult;

final class AuthOrchestratorService implements AuthOrchestratorContract
{
    /** @param iterable<AuthProviderContract> $providers */
    public function __construct(private readonly iterable $providers) {}

    public function authenticate(string $identifier, string $password): AuthenticationResult
    {
        foreach ($this->providers as $provider) {
            $result = $provider->authenticate($identifier, $password);

            if ($result->status !== AuthenticationStatus::NotFound) {
                return $result;
            }
        }

        return new AuthenticationResult(AuthenticationStatus::NotFound, 'pipeline');
    }
}
