<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Admin\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Rimba\Who\Services\SecurityCacheGenerator;
use Rimba\Who\Services\SecuritySettingsService;
use Throwable;
use UnitEnum;

final class SecurityPolicies extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static ?string $navigationLabel =
        'Security Policies';

    protected static ?string $title =
        'Security Policies';

    protected static string|UnitEnum|null $navigationGroup =
        'Who';

    protected string $view =
        'bites::admin.allowed-pages';

    private const ROUTES_FILE =
        'bootstrap/cache/rimba/routes.php';

    public ?array $data = [];

    /*
     * Public so Livewire preserves the refreshed routes between requests.
     *
     * @var array<int, string>
     */
    public array $routes = [];

    public function mount(
        SecuritySettingsService $settings,
    ): void {
        $this->routes = $this->loadRoutes();

        $this->form->fill(
            $settings->all(),
        );
    }

    public function form(
        Schema $schema,
    ): Schema {
        return $schema
            ->components([
                $this->policySection(
                    SecuritySettingsService::AUTHENTICATED,
                    'Authenticated',
                    'Routes available to authenticated users.',
                    collapsed: true,
                ),

                $this->policySection(
                    SecuritySettingsService::TWO_FACTOR_VERIFIED,
                    'Two-Factor Verified',
                    'Routes requiring completed two-factor verification.',
                    collapsed: true,
                ),

                $this->policySection(
                    SecuritySettingsService::FACE_VERIFIED,
                    'Face Verified',
                    'Sensitive routes requiring recent face verification.',
                    collapsed: false,
                ),
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
                ->label('Save Policies')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action(
                    fn () => $this->savePolicies(),
                ),
        ];
    }

    public function refreshRoutes(): void
    {
        try {
            $this->routes = app(
                SecurityCacheGenerator::class,
            )->generateRoutes();

            /*
             * Refill the existing state so selections remain unchanged
             * while dynamic CheckboxList options use the refreshed routes.
             */
            $this->form->fill(
                $this->data ?? [],
            );

            $this->success(
                'Routes refreshed',
                sprintf(
                    '%d Filament routes discovered.',
                    count($this->routes),
                ),
            );
        } catch (Throwable $throwable) {
            report($throwable);

            $this->failure(
                'Unable to refresh routes',
                $throwable->getMessage(),
            );
        }
    }

    public function savePolicies(): void
    {
        try {
            $state = $this->form->getState();

            $policies = app(
                SecuritySettingsService::class,
            )->save($state);

            app(
                SecurityCacheGenerator::class,
            )->generateSecurityPolicies();

            $this->form->fill($policies);

            $this->success(
                'Security policies saved',
                'The database settings and compiled security cache have been updated.',
            );
        } catch (Throwable $throwable) {
            report($throwable);

            $this->failure(
                'Unable to save security policies',
                $throwable->getMessage(),
            );
        }
    }

    private function policySection(
        string $field,
        string $heading,
        string $description,
        bool $collapsed,
    ): Section {
        $section = Section::make($heading)
            ->description($description)
            ->collapsible()
            ->schema([
                $this->securityField($field),
            ]);

        if ($collapsed) {
            $section->collapsed();
        }

        return $section;
    }

    private function securityField(
        string $field,
    ): CheckboxList {
        return CheckboxList::make($field)
            ->hiddenLabel()
            ->options(
                fn (): array => $this->routeOptions(),
            )
            ->searchable()
            ->bulkToggleable()
            ->columns(1);
    }

    private function loadRoutes(): array
    {
        $file = base_path(
            self::ROUTES_FILE,
        );

        if (! file_exists($file)) {
            return [];
        }

        $routes = require $file;

        return is_array($routes)
            ? $routes
            : [];
    }

    private function routeOptions(): array
    {
        return collect($this->routes)
            ->filter(
                static fn (mixed $route): bool => is_string($route)
                    && $route !== '',
            )
            ->unique()
            ->sort(
                SORT_NATURAL | SORT_FLAG_CASE,
            )
            ->mapWithKeys(
                static fn (string $route): array => [
                    $route => $route,
                ],
            )
            ->all();
    }

    private function success(
        string $title,
        ?string $body = null,
    ): void {
        Notification::make()
            ->success()
            ->title($title)
            ->body($body)
            ->send();
    }

    private function failure(
        string $title,
        ?string $body = null,
    ): void {
        Notification::make()
            ->danger()
            ->title($title)
            ->body($body)
            ->send();
    }
}
