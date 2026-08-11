<?php

declare(strict_types=1);

namespace Rimba\Who\Enums;

enum PanelId: string
{
    case Lobby = 'lobby';
    case Staff = 'staff';
    case StaffSensitive = 'staff-sensitive';
    case Team = 'team';
    case Admin = 'admin';
}
