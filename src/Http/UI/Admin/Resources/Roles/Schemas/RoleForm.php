<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Admin\Resources\Roles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('permissions')
                    ->multiple()
                    ->preload()
                    ->relationship(name: 'permissions', titleAttribute: 'name'),
            ]);
    }
}
