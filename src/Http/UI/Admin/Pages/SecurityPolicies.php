<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Admin\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Rimba\Who\Services\RouteDiscoveryService;

final class SecurityPolicies extends Page
{
    protected static ?string $navigationLabel = 'Security Policies';

    protected static string|\UnitEnum|null $navigationGroup = 'Who';

    public array $faceVerified = [];

    public function mount(): void
    {
        $file =
            base_path(
                'bootstrap/cache/who-security.php'
            );

        if (! file_exists($file)) {
            return;
        }

        $data = require $file;

        $this->faceVerified =
            $data['face_verified'] ?? [];
    }

    public function form(
        Schema $schema
    ): Schema {

        return $schema->components([

            CheckboxList::make(
                'faceVerified'
            )
                ->options(
                    collect(
                        app(
                            RouteDiscoveryService::class
                        )->discover()
                    )
                        ->mapWithKeys(
                            fn ($route): array => [
                                $route => $route,
                            ]
                        )
                        ->toArray()
                )
                ->columns(1)
                ->searchable(),

        ]);
    }

    protected function getHeaderActions(): array
    {
        return [

            Action::make('save')
                ->action(
                    fn () => $this->savePolicies()
                ),

        ];
    }

    public function savePolicies(): void
    {
        $contents = '<?php return '.
            var_export([
                'face_verified' => $this->faceVerified,
            ], true)
            .';';

        file_put_contents(
            base_path(
                'bootstrap/cache/who-security.php'
            ),
            $contents
        );
    }
}
