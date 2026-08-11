<?php

declare(strict_types=1);

namespace Rimba\Who\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;

final class ResetUserPassword
{
    public function handle(Authenticatable $user, string $password): void
    {
        $user->forceFill(['password' => Hash::make($password)])->save();
    }
}
