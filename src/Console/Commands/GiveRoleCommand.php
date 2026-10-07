<?php

declare(strict_types=1);

namespace Rimba\Who\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Rimba\People\Models\Staff;

#[Description('Assign a specific security role to a staff member')]
#[Signature('rimba:give-role {--s= : The staff number} {--r= : The role name}')]
class GiveRoleCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): ?int
    {
        $staffNo = $this->option('s');
        $roleName = $this->option('r');

        // 1. Validation for missing arguments
        if (! $staffNo || ! $roleName) {
            $this->error('Missing parameters! Usage: php artisan rimba:give-role -s <staff_no> -r <role_name>');

            return Command::FAILURE;
        }

        // 2. Find the staff member
        $staff = Staff::where('staff_no', $staffNo)->first();

        if (! $staff) {
            $this->error("Staff member with number [{$staffNo}] could not be found.");

            return Command::FAILURE;
        }

        // 3. Assign the role
        try {
            // Spatie's assignRole can take strings, arrays, or Role objects
            $staff->assignRole($roleName);
            $displayName = $staff->name ?: $staffNo;
            $this->info(
                "Successfully assigned the '{$roleName}' role to staff member {$displayName}."
            );
        } catch (\Exception $exception) {
            $this->error('Failed to assign role: '.$exception->getMessage());

            return Command::FAILURE;
        }

        return null;
    }
}
