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
                'staff' => null,
                'roles' => [],
            ];
        }

        $user = User::with([
            'staff.agreement.jobPosition.orgUnit',
        ])->find($user->getKey());

        $staff = $user?->staff;

        return [
            'user' => $user,
            'staff' => $staff,
            'roles' => $staff?->getRoleNames()->toArray() ?? [],
        ];
    }
}
