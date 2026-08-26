<?php

declare(strict_types=1);

namespace Rimba\Who\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Rimba\Who\Models\AuthenticationAttempt;
use Rimba\Who\Models\UserAuth;
use SensitiveParameter;

trait HasUserAuth
{
    public function userAuth(): HasOne
    {
        return $this->hasOne(UserAuth::class);
    }

    public function authenticationAttempts(): HasMany
    {
        return $this->hasMany(AuthenticationAttempt::class);
    }

    public function getAppAuthenticationSecret(): ?string
    {
        return $this->userAuth->totp_secret;
    }

    public function saveAppAuthenticationSecret(#[SensitiveParameter] ?string $secret): void
    {
        $this->userAuth->totp_secret = $secret;
        $this->save();
    }

    public function getAppAuthenticationHolderName(): string
    {
        return $this->email;
    }
}
