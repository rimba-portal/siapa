<?php

declare(strict_types=1);

namespace Rimba\Who\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Rimba\Who\Enums\AuthenticationStatus;

final readonly class AuthenticationResult
{
    public function __construct(public AuthenticationStatus $status, public string $provider, public ?Authenticatable $user = null, public ?string $reason = null) {}

    public function succeeded(): bool
    {
        return $this->status === AuthenticationStatus::Success;
    }
}
