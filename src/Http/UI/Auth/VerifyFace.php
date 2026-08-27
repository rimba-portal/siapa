<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Auth;

use Filament\Pages\SimplePage;
use Filament\Schemas\Schema;
use Rimba\Who\Components\FaceAuth;
use Rimba\Who\Contracts\FaceVerifierContract;
use Rimba\Who\Contracts\PanelAccessResolverContract;

class VerifyFace extends SimplePage
{
    protected string $view = 'bites::auth.verify-face';

    public ?array $data = [];

    public function mount(): void
    {
        $staffNo = auth()->user()?->staff?->staff_no;

        abort_if(
            blank($staffNo),
            403,
            'Staff number not found.'
        );

        $this->form->fill([
            'staff_no' => $staffNo,
        ]);
    }

    public function form(
        Schema $schema,
    ): Schema {

        return $schema
            ->components([
                FaceAuth::make('face')
                    ->staffNo(
                        fn () => $this->data['staff_no'] ?? null
                    ),
            ])
            ->statePath('data');
    }

    public function faceMatched(): void
    {
        app(FaceVerifierContract::class)
            ->recordVerification(
                auth()->user(),
                request()->ip(),
                request()->userAgent(),
            );

        $intendedUrl = session()->pull(
            'face_auth.intended_url'
        );

        if ($intendedUrl) {
            $this->redirect($intendedUrl);

            return;
        }

        $destination = app(
            PanelAccessResolverContract::class
        )->destinationFor(
            auth()->user()
        );

        $this->redirect(
            filament()
                ->getPanel($destination)
                ->getUrl()
        );
    }
}
