<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Admin\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Rimba\Who\Services\SecurityCacheGenerator;

final class SecurityPolicies extends Page implements HasSchemas
{
    protected static ?string $navigationLabel =
        'Security Policies';

    protected static ?string $title =
        'Security Policies';

    protected static string|\UnitEnum|null $navigationGroup =
        'Who';

    protected string $view =
        'bites::admin.allowed-pages';

    private const CACHE_DIR =
        'bootstrap/cache/rimba';

    private const ROUTES_FILE =
        self::CACHE_DIR.'/who-routes.php';

    private const SECURITY_FILE =
        self::CACHE_DIR.'/who-security.php';

    public ?array $data = [];

    private array $routes = [];

    public function mount(): void
    {
        $this->routes = $this->loadRoutes();

        $this->data = array_merge(
            $this->defaultPolicies(),
            $this->loadPolicies(),
        );
    }

    public function form(
        Schema $schema,
    ): Schema {

        return $schema
            ->components([

                Section::make('Authenticated')
                    ->description(
                        sprintf(
                            '%d routes configured',
                            count(
                                $this->data['authenticated'] ?? [],
                            ),
                        ),
                    )
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        $this->securityField(
                            'authenticated',
                        ),
                    ]),

                Section::make('Two Factor Verified')
                    ->description(
                        sprintf(
                            '%d routes configured',
                            count(
                                $this->data['two_factor_verified'] ?? [],
                            ),
                        ),
                    )
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        $this->securityField(
                            'two_factor_verified',
                        ),
                    ]),

                Section::make('Face Verified')
                    ->description(
                        sprintf(
                            '%d routes configured',
                            count(
                                $this->data['face_verified'] ?? [],
                            ),
                        ),
                    )
                    ->collapsible()
                    ->schema([
                        $this->securityField(
                            'face_verified',
                        ),
                    ]),

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
                ->action(
                    fn () => $this->refreshRoutes(),
                ),

            Action::make('save')
                ->label('Save')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action(
                    fn () => $this->savePolicies(),
                ),

        ];
    }

    protected function refreshRoutes(): void
    {
        app(
            SecurityCacheGenerator::class
        )->generate();

        $this->routes =
            $this->loadRoutes();

        $this->success(
            'Routes refreshed',
            sprintf(
                '%d routes discovered.',
                count($this->routes),
            ),
        );
    }

    public function savePolicies(): void
    {
        file_put_contents(
            base_path(
                self::SECURITY_FILE,
            ),
            $this->exportPhpArray(
                $this->data,
            ),
            LOCK_EX,
        );

        $this->success(
            'Security policies saved',
        );
        $this->refreshRoutes();
    }

    protected function securityField(
        string $field,
    ): CheckboxList {

        return CheckboxList::make($field)
            ->hiddenLabel()
            ->options(
                fn (): array => $this->routeOptions()
            )
            ->searchable()
            ->bulkToggleable()
            ->columns(1);
    }

    protected function defaultPolicies(): array
    {
        return [

            'authenticated' => [],

            'two_factor_verified' => [],

            'face_verified' => [],

        ];
    }

    protected function loadPolicies(): array
    {
        $file = base_path(
            self::SECURITY_FILE,
        );

        if (! file_exists($file)) {
            return [];
        }

        return require $file;
    }

    protected function loadRoutes(): array
    {
        $file = base_path(
            self::ROUTES_FILE,
        );

        if (! file_exists($file)) {
            return [];
        }

        return require $file;
    }

    protected function routeOptions(): array
    {
        return collect($this->routes)
            ->mapWithKeys(
                fn (string $route): array => [
                    $route => $route,
                ],
            )
            ->all();
    }

    protected function exportPhpArray(
        array $data,
    ): string {

        return '<?php'
            .PHP_EOL
            .PHP_EOL
            .'return '
            .var_export($data, true)
            .';'
            .PHP_EOL;
    }

    protected function success(
        string $title,
        ?string $body = null,
    ): void {

        Notification::make()
            ->success()
            ->title($title)
            ->body($body)
            ->send();
    }
}
