<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Admin\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Pages\Page;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;

final class SecurityPolicies extends Page implements HasSchemas
{
    protected static ?string $navigationLabel =
        'Security Policies';

    protected static string|\UnitEnum|null $navigationGroup =
        'Who';

    protected string $view =
        'bites::admin.allowed-pages';

    public ?array $data = [];

    protected array $routes = [];

    public function mount(): void
    {
        $this->routes = $this->loadRoutes();

        $securityFile = base_path(
            'bootstrap/cache/who-security.php'
        );

        $this->data = [
            'authenticated' => [],
            'two_factor_verified' => [],
            'face_verified' => [],
        ];

        if (! file_exists($securityFile)) {
            return;
        }

        $content = require $securityFile;

        $this->data = array_merge(
            $this->data,
            $content
        );
    }

    public function form(
        Schema $schema
    ): Schema {

        return $schema
            ->components([

                CheckboxList::make(
                    'authenticated'
                )
                    ->label('Authenticated')
                    ->options($this->routeOptions())
                    ->searchable()
                    ->columns(1),

                CheckboxList::make(
                    'two_factor_verified'
                )
                    ->label('Two Factor Verified')
                    ->options($this->routeOptions())
                    ->searchable()
                    ->columns(1),

                CheckboxList::make(
                    'face_verified'
                )
                    ->label('Face Verified')
                    ->options($this->routeOptions())
                    ->searchable()
                    ->columns(1),

            ])
            ->statePath('data');
    }

    protected function routeOptions(): array
    {
        return collect($this->routes)
            ->mapWithKeys(
                fn (string $route): array => [
                    $route => $route,
                ]
            )
            ->toArray();
    }

    protected function loadRoutes(): array
    {
        $file = base_path(
            'bootstrap/cache/rimba/who-routes.php'
        );

        if (! file_exists($file)) {
            return [];
        }

        return require $file;
    }

    protected function getHeaderActions(): array
    {
        return [

            Action::make('save')
                ->icon('heroicon-o-check')
                ->action(
                    fn () => $this->savePolicies()
                ),

        ];
    }

    public function savePolicies(): void
    {
        $contents =
            '<?php return '.
            var_export([
                'authenticated' => $this->data['authenticated'] ?? [],

                'two_factor_verified' => $this->data['two_factor_verified'] ?? [],

                'face_verified' => $this->data['face_verified'] ?? [],
            ], true)
            .';';

        file_put_contents(
            base_path(
                'bootstrap/cache/rimba/who-security.php'
            ),
            $contents
        );
    }
}
