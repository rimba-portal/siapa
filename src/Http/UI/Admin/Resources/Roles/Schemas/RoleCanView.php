<?php

declare(strict_types=1);

// declare(strict_types=1);

namespace Rimba\Who\Http\UI\Admin\Resources\Roles\Schemas;

use Bites\Organization\Structure\OrgUnit;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class RoleCanView
{
    /**
     * Build reusable **form** components (hidden Select + Action modal).
     *
     * @param  string  $relationship  The morphToMany relationship name on the model (default: 'attachableRoles')
     * @param  bool  $showSelect  Show the Select field instead of hiding it (default: false)
     * @param  string  $actionName  Unique action name to avoid collisions if you include multiple (default: 'choose_roles')
     * @param  string  $superUserRole  Role that can assigned to any roles, else can only assign to same OU roles
     * @param  int  $recordId  The ID of the record to resolve (default: 1)
     */
    public static function formComponents(
        string $relationship = 'attachableRoles',
        bool $showSelect = false,
        string $actionName = 'choose_roles',
        string $superUserRole = 'jt_developer',
        int $recordId = 1,
    ): array {
        // ----- Build all role options (labels), descriptions, and id sets -----
        [
            'userRoleOptions' => $userRoleOptions,
            'staffRoleOptions' => $staffRoleOptions,
            'jobRoleOptions' => $jobRoleOptions,
            'ouRoleOptions' => $ouRoleOptions,
            'myRoleOptions' => $myRoleOptions,
            'userRoleDescriptions' => $userRoleDescriptions,
            'staffRoleDescriptions' => $staffRoleDescriptions,
            'jobRoleDescriptions' => $jobRoleDescriptions,
            'ouRoleDescriptions' => $ouRoleDescriptions,
            'utRoleIds' => $utRoleIds,
            'stRoleIds' => $stRoleIds,
            'jtRoleIds' => $jtRoleIds,
            'ouRoleIds' => $ouRoleIds,
            'myRoleIds' => $myRoleIds,
        ] = self::buildRoleData();
        // dd(app(PermissionRegistrar::class)->getPermissionsTeamId());

        // dd($recordId);
        // ----- The action modal (choose roles) -----
        $chooseAction = Action::make($actionName)
            ->label('Choose Visible Roles')
            ->modalHeading('Select roles for this record')
            ->modalSubmitActionLabel('Apply')
            ->modalWidth('xl')

            // Preselect checkboxes from the **current record** relation
            ->fillForm(function ($model) use ($relationship, $utRoleIds, $stRoleIds, $jtRoleIds, $ouRoleIds, $myRoleIds): array {
                // dd();
                if (! $model instanceof Model) {
                    // No record yet (e.g., create page) or Filament passed a class-string.

                    return [
                        'show_more_info' => false,
                        'ut_role_ids' => [],
                        'st_role_ids' => [],
                        'jt_role_ids' => [],
                        'ou_role_ids' => [],
                        'my_role_ids' => [],
                    ];
                }

                $attached = $model->{$relationship}()->pluck('id')->toArray();

                return [
                    'show_more_info' => false,
                    'ut_role_ids' => array_values(array_intersect($attached, $utRoleIds)),
                    'st_role_ids' => array_values(array_intersect($attached, $stRoleIds)),
                    'jt_role_ids' => array_values(array_intersect($attached, $jtRoleIds)),
                    'ou_role_ids' => array_values(array_intersect($attached, $ouRoleIds)),
                    'my_role_ids' => array_values(array_intersect($attached, $myRoleIds)),
                ];
            })

            ->schema([
                Toggle::make('show_more_info')->label('Show more info')->default(false)->live(),
                Toggle::make('only_own_ous')->label('More roles')->default(false)->live()->dehydrated(false)
                    ->visible(fn () => Auth::user()?->hasRole($superUserRole) ?? false),
                Tabs::make('roleTabs')->tabs([
                    Tab::make('User')->schema([
                        CheckboxList::make('ut_role_ids')
                            ->label('Attributes (ut_*)')
                            ->options($userRoleOptions)
                            ->descriptions(
                                fn (Get $get): array => $get('show_more_info')
                                    ? array_filter($userRoleDescriptions, fn ($v): bool => ! is_null($v))
                                    : []
                            )
                            ->columns(fn (Get $get): int => $get('show_more_info') ? 1 : 3)
                            ->searchable(),
                    ])->visible(fn (Get $get): mixed => $get('only_own_ous')),
                    Tab::make('Staff')->schema([
                        CheckboxList::make('st_role_ids')
                            ->label('Attributes (st_*)')
                            ->options($staffRoleOptions)
                            ->descriptions(
                                fn (Get $get): array => $get('show_more_info')
                                    ? array_filter($staffRoleDescriptions, fn ($v): bool => ! is_null($v))
                                    : []
                            )
                            ->columns(fn (Get $get): int => $get('show_more_info') ? 1 : 3)
                            ->searchable(),
                    ])->visible(fn (Get $get): mixed => $get('only_own_ous')),
                    Tab::make('Job Post')->schema([
                        CheckboxList::make('jt_role_ids')
                            ->label('Attributes (jt_*)')
                            ->options($jobRoleOptions)
                            ->descriptions(
                                fn (Get $get): array => $get('show_more_info')
                                    ? array_filter($jobRoleDescriptions, fn ($v): bool => ! is_null($v))
                                    : []
                            )
                            ->columns(fn (Get $get): int => $get('show_more_info') ? 1 : 3)
                            ->searchable(),
                    ])->visible(fn (Get $get): mixed => $get('only_own_ous')),
                    Tab::make('Org Unit')->schema([
                        CheckboxList::make('ou_role_ids')
                            ->label('Attributes (ou_*)')
                            ->options($ouRoleOptions)
                            ->descriptions(
                                fn (Get $get): array => $get('show_more_info')
                                    ? array_filter($ouRoleDescriptions, fn ($v): bool => ! is_null($v))
                                    : []
                            )
                            ->columns(fn (Get $get): int => $get('show_more_info') ? 1 : 1)
                            ->searchable(),
                    ])->visible(fn (Get $get): mixed => $get('only_own_ous')),
                    Tab::make('Own OUs')->schema([
                        CheckboxList::make('my_role_ids')
                            ->label('Attributes (ou_*)')
                            ->options($myRoleOptions)
                            ->descriptions(
                                fn (Get $get): array => $get('show_more_info')
                                    ? array_filter($ouRoleDescriptions, fn ($v): bool => ! is_null($v))
                                    : []
                            )
                            //  ->disableOptionWhen(fn(string $value) => ! in_array($value,["96","97","98","99","100"], true))
                            ->columns(fn (Get $get): int => $get('show_more_info') ? 1 : 1)
                            ->searchable(),
                    ])->hidden(fn (Get $get): mixed => $get('only_own_ous')),
                ]),
            ])

            // Persist to DB and refresh the Select state so UI updates
            ->action(function (array $data, $model = null) use ($relationship): void {
                // Only proceed when we truly have a record instance.
                // if (! $model instanceof \Illuminate\Database\Eloquent\Model) {
                //     // No record available in this context (e.g., create page or class-string was provided).
                //     // Bail out quietly (or you could throw a Notification here if you prefer).
                //     return;
                // }

                $allRoleIds = array_values(array_unique(array_merge(
                    $data['ut_role_ids'] ?? [],
                    $data['st_role_ids'] ?? [],
                    $data['jt_role_ids'] ?? [],
                    $data['ou_role_ids'] ?? [],
                    $data['my_role_ids'] ?? [],
                )));
                dd($model);
                $model->{$relationship}()->sync($allRoleIds);
                $model->loadMissing($relationship);
            })
            ->stickyModalHeader()
            ->stickyModalFooter();

        $textEntry = TextEntry::make('Can View')
            ->listWithLineBreaks()
            ->html()
            ->getStateUsing(function ($record) use ($relationship) {
                $roles = $record->{$relationship}
                    ->map(fn (Role $role): string => self::labelFromRole($role)); // Eloquent Collection of Role
                if (! $roles) {
                    return '';
                }

                return $roles;
            });

        return [
            Section::make('Choose Roles')
                ->description('Roles able to View, Edit')
                ->columnSpanFull()
                ->schema([
                    $chooseAction,
                    $textEntry,
                ]),
        ];
    }

    /**
     * Internal: builds role options, descriptions, and id sets.
     */
    protected static function buildRoleData(): array
    {
        // $teamId = app(PermissionRegistrar::class)->getPermissionsTeamId();
        $teamId = 15;

        $utRoles = Role::query()
            ->where('name', 'like', 'ut_%')
            ->orderBy('name')
            ->get(['id', 'name', 'description']);

        $stRoles = Role::query()
            ->where('name', 'like', 'st_%')
            ->orderBy('name')
            ->get(['id', 'name', 'description']);

        $jtRoles = Role::query()
            ->where('name', 'like', 'jt_%')
            ->orderBy('name')
            ->get(['id', 'name', 'description']);

        $ouRoles = Role::query()
            ->where('name', 'like', 'ou_%')
            ->orderBy('team_id')
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'team_id']);

        $myRoles = Role::query()
            ->where('team_id', $teamId)
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'team_id']);

        OrgUnit::query()->pluck('code', 'id')->toArray();

        $userRoleOptions = $utRoles->mapWithKeys(function (Role $role): array {
            return [$role->id => self::labelFromRole($role)];
        })->toArray();

        $staffRoleOptions = $stRoles->mapWithKeys(function (Role $role): array {
            return [$role->id => self::labelFromRole($role)];
        })->toArray();

        $jobRoleOptions = $jtRoles->mapWithKeys(function (Role $role): array {
            return [$role->id => self::labelFromRole($role)];
        })->toArray();
        $ouRoleOptions = $ouRoles->mapWithKeys(function (Role $role): array {
            return [$role->id => self::labelFromRole($role)];
        })->toArray();
        $myRoleOptions = $myRoles->mapWithKeys(function (Role $role): array {
            return [$role->id => self::labelFromRole($role)];
        })->toArray();

        // $value = 99;
        // $x = fn(string $value): bool => in_array($value, $ownOuRoles);
        // dump(in_array($value,$ownOuRoles,true));
        // dd($ownOuRoles);
        // dd($staffRoleOptions);

        // Descriptions (optional markdown, ID => HtmlString|null)
        $userRoleDescriptions = $utRoles->mapWithKeys(function (Role $role): array {
            return [$role->id => $role->description
                ? new HtmlString(Str::inlineMarkdown($role->description))
                : null];
        })->toArray();

        $staffRoleDescriptions = $stRoles->mapWithKeys(function (Role $role): array {
            return [$role->id => $role->description
                ? new HtmlString(Str::inlineMarkdown($role->description))
                : null];
        })->toArray();

        $jobRoleDescriptions = $jtRoles->mapWithKeys(function (Role $role): array {
            return [$role->id => $role->description
                ? new HtmlString(Str::inlineMarkdown($role->description))
                : null];
        })->toArray();

        $ouRoleDescriptions = $ouRoles->mapWithKeys(function (Role $role): array {
            return [$role->id => $role->description
                ? new HtmlString(Str::inlineMarkdown($role->description))
                : null];
        })->toArray();

        return [
            'userRoleOptions' => $userRoleOptions,
            'staffRoleOptions' => $staffRoleOptions,
            'jobRoleOptions' => $jobRoleOptions,
            'ouRoleOptions' => $ouRoleOptions,
            'myRoleOptions' => $myRoleOptions,
            'userRoleDescriptions' => $userRoleDescriptions,
            'staffRoleDescriptions' => $staffRoleDescriptions,
            'jobRoleDescriptions' => $jobRoleDescriptions,
            'ouRoleDescriptions' => $ouRoleDescriptions,
            'utRoleIds' => array_keys($userRoleOptions),
            'stRoleIds' => array_keys($staffRoleOptions),
            'jtRoleIds' => array_keys($jobRoleOptions),
            'ouRoleIds' => array_keys($ouRoleOptions),
            'myRoleIds' => array_keys($myRoleOptions),
        ];
    }

    private static function labelFromName(string $name, ?int $teamId = null): string
    {
        $orgUnitCodesById = OrgUnit::query()->pluck('code', 'id')->toArray();
        // pick the prefix
        if (Str::startsWith($name, 'ut_')) {
            $raw = Str::after($name, 'ut_');

            return (string) Str::of($raw)->replace('_', ' ')->title();
        }

        if (Str::startsWith($name, 'st_')) {
            $raw = Str::after($name, 'st_');

            return (string) Str::of($raw)->replace('_', ' ')->title();
        }

        if (Str::startsWith($name, 'jt_')) {
            $raw = Str::after($name, 'jt_category_');

            return (string) Str::of($raw)->replace('_', ' ')->title();
        }

        if (Str::startsWith($name, 'ou_')) {
            $raw = Str::after($name, 'ou_');
            $base = (string) Str::of($raw)->replace('_', ' ')->title();
            $code = $teamId ? ($orgUnitCodesById[$teamId] ?? 'OU#'.$teamId) : 'GLOBAL';

            return sprintf('%s [OU: %s]', $base, $code);
        }

        // fallback when no known prefix
        return (string) Str::of($name)->replace('_', ' ')->title();
    }

    /**
     * Preferred when you already have a Role model.
     */
    private static function labelFromRole(Role $role): string
    {
        // Some Spatie roles store team on pivot; prefer model's team_id if available
        $teamId = $role->team_id ?? ($role->pivot->team_id ?? null);

        return self::labelFromName($role->name, $teamId);
    }

    /**
     * Returns a Closure suitable for Table::modifyQueryUsing().
     *
     * @param  array{
     *   relation?: string,               // relation to eager load (default: 'attachableRoles:id,name')
     *   sessionOrgKey?: string,          // session key to resolve current OU (default: 'current_org_unit')
     *   ouModel?: class-string,          // OU model class (default: \Bites\Organization\Structure\OrgUnit::class)
     * } $options
     */
    public static function tableVisibilityModifier(array $options = []): Closure
    {
        $relation = $options['relation'] ?? 'attachableRoles:id,name';
        $sessionOrgKey = $options['sessionOrgKey'] ?? 'current_org_unit';
        $ouModel = $options['ouModel'] ?? OrgUnit::class;
        $su = $options['su'] ?? 0;

        return function (Builder $builder) use (
            $relation,
            $sessionOrgKey,
            $ouModel,
            $su
        ): void {
            $user = Auth::user();
            if ($user->staff->staff_number == $su) {
                return;
            }

            $builder->with($relation);
            $orgUnitId = session($sessionOrgKey);
            $orgUnit = $orgUnitId ? $ouModel::find($orgUnitId) : null;

            // Filter via your scope:
            if ($orgUnit) {
                $builder->visibleToUserByUserTeams($user, $orgUnit);
            } else {
                $builder->visibleToUser($user);
            }
        };
    }

    /**
     * Helper: parse "relation:columns" like 'attachableRoles:id,name' or just 'attachableRoles'.
     */
    protected static function parseRelationAndColumns(string $relation): array
    {
        if (str_contains($relation, ':')) {
            [$name, $cols] = explode(':', $relation, 2);
            $columns = array_map('trim', explode(',', $cols));

            return [trim($name), $columns];
        }

        return [trim($relation), null];
    }
}
