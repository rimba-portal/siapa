<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Admin\Resources\Permissions\Pages;

use Filament\Resources\Pages\CreateRecord;
use Rimba\Who\Http\UI\Admin\Resources\Permissions\PermissionResource;

class CreatePermission extends CreateRecord
{
    protected static string $resource = PermissionResource::class;
}
