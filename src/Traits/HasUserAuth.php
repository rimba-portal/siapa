<?php

declare(strict_types=1);

namespace Rimba\Who\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Rimba\Who\Models\AuthenticationAttempt;
use Rimba\Who\Models\UserAuth;

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
}
