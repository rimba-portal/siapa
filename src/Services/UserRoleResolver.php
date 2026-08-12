<?php

declare(strict_types=1);

namespace Rimba\Who\Services;

use App\Models\User;
use Rimba\Attributing\Models\AttributeDefinition;
use Spatie\Permission\Models\Role;

class UserRoleResolver
{
    public function sync(User $user): void
    {
        $staff = $user->staff;

        if (! $staff) {
            return;
        }

        $abacKeys = AttributeDefinition::query()
            ->where('family', 'person')
            ->where('is_abac', true)
            ->pluck('key')
            ->all();

        $abacRoles = [];

        foreach ($staff->personAttributes as $attribute) {

            if (! in_array($attribute->key, $abacKeys, true)) {
                continue;
            }

            $role = sprintf(
                '%s.%s',
                $attribute->key,
                $attribute->value
            );

            Role::findOrCreate($role);

            $abacRoles[] = $role;
        }

        $manualRoles = $user->roles
            ->pluck('name')
            ->reject(function ($roleName) use ($abacKeys): bool {

                foreach ($abacKeys as $abacKey) {
                    if (str_starts_with($roleName, $abacKey.'.')) {
                        return true;
                    }
                }

                return false;
            })
            ->values()
            ->all();

        $user->syncRoles([
            ...$manualRoles,
            ...$abacRoles,
        ]);
    }
}
