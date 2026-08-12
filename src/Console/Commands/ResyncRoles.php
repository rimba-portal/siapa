<?php

declare(strict_types=1);

namespace Rimba\Who\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Rimba\Who\Services\UserRoleResolver;

#[Description('Resync ABAC roles')]
#[Signature('rimba:resync-roles')]
class ResyncRoles extends Command
{
    public function handle(
        UserRoleResolver $resolver
    ): int {

        User::query()
            ->with('staff.personAttributes')
            ->chunk(
                100,
                fn ($users) => $users->each(
                    fn (User $user) => $resolver->sync($user)
                )
            );

        return self::SUCCESS;
    }
}
