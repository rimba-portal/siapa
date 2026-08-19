<?php

declare(strict_types=1);

namespace Rimba\Who\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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

            $existingUser = $model::query()
                ->whereRaw(
                    'LOWER(email) = ?',
                    [mb_strtolower($email)]
                )
                ->first();

            if ($existingUser) {
                throw ValidationException::withMessages([
                    'email' => 'Email already exists.',
                ]);
            }

            $existingAuth = UserAuth::query()
                ->where('auth_provider', 'local')
                ->where(
                    'auth_identifier',
                    strtolower($authIdentifier)
                )
                ->exists();

            if ($existingAuth) {
                throw ValidationException::withMessages([
                    'username' => 'Username already exists.',
                ]);
            }

            $user = $model::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
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
