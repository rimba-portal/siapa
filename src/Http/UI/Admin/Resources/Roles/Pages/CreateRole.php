<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Admin\Resources\Roles\Pages;

use Filament\Resources\Pages\CreateRecord;
use Rimba\Who\Http\UI\Admin\Resources\Roles\RoleResource;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;
}
