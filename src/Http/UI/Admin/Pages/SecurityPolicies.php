<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Admin\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Rimba\Who\Services\SecurityCacheGenerator;

final class SecurityPolicies extends Page implements HasSchemas
{
    protected static ?string $navigationLabel =
        'Security Policies';

    protected static string|\UnitEnum|null $navigationGroup =
        'Who';

    protected string $view =
        'bites::admin.allowed-pages';

    public ?array $data = [];

    private array $routes = [];

    public function mount(): void
    {
        $this->routes = $this->loadRoutes();

        $securityFile = base_path(
            'bootstrap/cache/rimba/who-security.php'
        );

        $this->data = [
            'authenticated' => [],
            'two_factor_verified' => [],
            'face_verified' => [],
        ];

        if (! file_exists($securityFile)) {
            return;
        }

        $this->data = array_merge(
            $this->data,
            require $securityFile,
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
                    ->options(
                        $this->routeOptions()
                    )
                    ->searchable()
                    ->columns(1),

                CheckboxList::make(
                    'two_factor_verified'
                )
                    ->label('Two Factor Verified')
                    ->options(
                        $this->routeOptions()
                    )
                    ->searchable()
                    ->columns(1),

                CheckboxList::make(
                    'face_verified'
                )
                    ->label('Face Verified')
                    ->options(
                        $this->routeOptions()
                    )
                    ->searchable()
                    ->columns(1),

            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [

            Action::make('refreshRoutes')
                ->label('Refresh Routes')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function (): void {

                    app(
                        SecurityCacheGenerator::class
                    )->generate();

                    $this->routes =
                        $this->loadRoutes();

                    Notification::make()
                        ->success()
                        ->title(
                            'Routes refreshed'
                        )
                        ->body(
                            sprintf(
                                '%d routes discovered.',
                                count($this->routes)
                            )
                        )
                        ->send();
                }),

            Action::make('save')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action(
                    fn () => $this->savePolicies()
                ),

        ];
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

    public function savePolicies(): void
    {
        $contents =
            "<?php\n\nreturn "
            .var_export(
                [
                    'authenticated' => $this->data['authenticated'] ?? [],
                    'two_factor_verified' => $this->data['two_factor_verified'] ?? [],
                    'face_verified' => $this->data['face_verified'] ?? [],
                ],
                true,
            )
            .";\n";

        file_put_contents(
            base_path(
                'bootstrap/cache/rimba/who-security.php'
            ),
            $contents,
            LOCK_EX,
        );

        Notification::make()
            ->success()
            ->title(
                'Security policies saved'
            )
            ->send();
    }
}
