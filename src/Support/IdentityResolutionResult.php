<?php

declare(strict_types=1);

namespace Rimba\Who\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Rimba\Who\Models\UserAuth;

final readonly class IdentityResolutionResult
{
    public function __construct(public string $identifier, public ?Authenticatable $user = null, public ?UserAuth $userAuth = null) {}

    public function exists(): bool
    {
        return $this->user instanceof Authenticatable && $this->userAuth instanceof UserAuth;
    }

    public function isNew(): bool
    {
        return ! $this->exists();
    }

    public function provider(): ?string
    {
        return $this->userAuth?->auth_provider;
    }
}
