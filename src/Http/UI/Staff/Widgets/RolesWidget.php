<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Staff\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class RolesWidget extends Widget
{
    protected string $view = 'bites::staff.roles-widget';

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Your Roles';

    // Optional: show on dashboard only
    // protected int|string|array $columnSpan = 'full'; // or 1/2/3 etc.

    public function getRoles(): array
    {
        $user = Auth::user();

        return $user->staff->getRoleNames()->toArray() ?? [];
    }
}
