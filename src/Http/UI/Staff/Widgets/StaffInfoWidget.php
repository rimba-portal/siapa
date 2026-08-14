<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Staff\Widgets;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

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
                'staffName' => '-',
                'staff_number' => '-',
                'jobPositionName' => '-',
                'orgUnitName' => '-',
                'roles' => [],
            ];
        }

        $user = User::with([
            'staff.agreement.jobPosition.orgUnit',
        ])->find($user->getKey());

        $staff = $user?->staff;

        $agreement = $staff?->agreement;

        $jobPosition = $agreement?->jobPosition;

        return [
            'user' => $user,
            'staffName' => $staff?->name ?? '-',
            'staff_number' => $staff?->staff_no ?? '-',
            'jobPositionName' => $jobPosition?->title ?? '-',
            'orgUnitName' => $jobPosition?->orgUnit?->name ?? '-',
            'roles' => $staff?->getRoleNames()->toArray() ?? [],
        ];
    }
}
