<?php

declare(strict_types=1);

namespace Rimba\Who\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Rimba\Who\Models\UserAuth;

final class LoginUser
{
    public function handle(Authenticatable $user, bool $remember = false): void
    {
        Auth::login($user, $remember);
        request()->session()->regenerate();
        UserAuth::query()->where('user_id', $user->getAuthIdentifier())->update(['last_login_at' => now()]);
    }
}
