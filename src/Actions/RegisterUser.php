<?php

declare(strict_types=1);

namespace Rimba\Who\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Rimba\Who\Models\UserAuth;

final readonly class RegisterUser
{
    public function __construct(
        private LinkStaffToUser $linkStaffToUser,
    ) {}

    public function handle(
        string $name,
        string $email,
        string $password,
        string $authIdentifier,
    ): Authenticatable {
        $model = config('auth.providers.users.model');

        return DB::transaction(function () use (
            $model,
            $name,
            $email,
            $password,
            $authIdentifier,
        ) {
            $user = $model::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            UserAuth::query()->create([
                'user_id' => $user->getAuthIdentifier(),
                'auth_provider' => 'local',
                'auth_identifier' => strtolower($authIdentifier),
            ]);

            $this->linkStaffToUser->handle(
                $user,
                $authIdentifier,
            );

            return $user;
        });
    }
}
