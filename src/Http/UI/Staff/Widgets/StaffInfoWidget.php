<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Staff\Widgets;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Rimba\People\Models\Staff;
use Spatie\Permission\Models\Role;

class StaffInfoWidget extends Widget
{
    protected string $view = 'bites::staff.staff-info-widget';

    // Control ordering relative to other widgets (same as AccountWidget example)
    protected static ?int $sort = -2;

    protected int|string|array $columnSpan = 3;

    // Render immediately (no skeleton/loading state)
    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return Filament::auth()->check();
    }

    /**
     * Pass view data to Blade similarly to AccountWidget.
     */
    protected function getViewData(): array
    {
        /** @var User|null $user */
        $user = Filament::auth()->user();

        if (! $user) {
            return [
                'user' => null,
                'orgUnitName' => '-',
                'jobPositionName' => '-',
            ];
        }

        // Eager-load staff with org unit (active only) and job position
        $user = User::with([
            'staff.orgUnit' => fn ($q) => $q->where('status', 'active'),
            'staff.jobPosition',
        ])->find($user->getKey());
        // dd($user);
        // dd($user?->staff?->jobPosition?->superior_id);
        $staff_name = $user?->staff?->name;
        $staff_number = $user?->staff?->staff_number;
        $jobPositionName = $user?->staff?->jobPosition?->title ?? '-';
        $orgUnitName = $user?->staff?->jobPosition?->orgUnit?->name ?? '-';
        $reportingTo = $user?->staff?->jobPosition?->superior_id;
        Staff::find($reportingTo);
        // $roleids = array_values(Auth::user()->roles->pluck('id')->toArray()) ?? [];
        // $roles = Role::withoutGlobalScopes()->whereIn('id', $roleids);
        $roles = $user->getRoleNames()->toArray() ?? [];

        // dd($superior);
        return [
            'user' => $user,
            'staffName' => $staff_name,
            'roles' => $roles,
            'staff_number' => $staff_number,
            'orgUnitName' => $orgUnitName,
            'jobPositionName' => $jobPositionName,
        ];
    }
}
