<?php

declare(strict_types=1);

namespace Rimba\Who\Console\Commands;

use Illuminate\Console\Command;
use Rimba\People\Models\Staff;

class GiveRoleCommand extends Command
{
    /**
     * The name and signature of the console command.
     * {--s=} expects a value for staff_no, {--r=} expects a value for the role
     */
    protected $signature = 'rimba:give-role {--s= : The staff number} {--r= : The role name}';

    /**
     * The console command description.
     */
    protected $description = 'Assign a specific security role to a staff member';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $staffNo = $this->option('s');
        $roleName = $this->option('r');

        // 1. Validation for missing arguments
        if (!$staffNo || !$roleName) {
            $this->error('Missing parameters! Usage: php artisan rimba:give-role -s <staff_no> -r <role_name>');
            return Command::FAILURE;
        }

        // 2. Find the staff member
        $staff = Staff::where('staff_no', $staffNo)->first();

        if (!$staff) {
            $this->error("Staff member with number [{$staffNo}] could not be found.");
            return Command::FAILURE;
        }

        // 3. Assign the role
        try {
            // Spatie's assignRole can take strings, arrays, or Role objects
            $staff->assignRole($roleName); 
            
            $this->info("Successfully assigned the '{$roleName}' role to staff member {$staff->name ?? $staffNo}.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to assign role: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
