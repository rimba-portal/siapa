<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Admin\Resources\Permissions\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Rimba\Who\Http\UI\Admin\Resources\Permissions\PermissionResource;
use Spatie\Permission\Models\Permission;

class ListPermissions extends ListRecords
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $groups = Permission::query()
            ->where('guard_name', 'web')
            ->get()
            ->groupBy(fn (Permission $permission) => $permission->action ?: 'other');

        $tabs = [
            'all' => Tab::make('All')
                ->badge($groups->flatten()->count())
                ->badgeColor('primary')
                ->icon('heroicon-o-rectangle-stack')
                ->modifyQueryUsing(
                    fn (Builder $query) => $query
                        ->where('guard_name', 'web')
                ),
        ];

        foreach ($groups as $action => $permissions) {
            $tabs[$action] = Tab::make(
                Str::headline($action)
            )
                ->badge($permissions->count())
                ->modifyQueryUsing(
                    fn (Builder $query) => $query
                        ->where('guard_name', 'web')
                        ->where('action', $action)
                );
        }

        return $tabs;
    }
}
