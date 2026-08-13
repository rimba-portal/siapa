<?php

declare(strict_types=1);

namespace Rimba\Who\Contracts;

use Rimba\Who\Support\AuthenticationResult;
use Rimba\Who\Support\ExternalIdentity;

interface IdentityAuthenticatorContract
{
    public function name(): string;

    public function authenticateExisting(string $identifier, string $password): AuthenticationResult;

    public function authenticateExternal(ExternalIdentity $identity, string $password): AuthenticationResult;
}
