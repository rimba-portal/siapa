<?php

declare(strict_types=1);

namespace Rimba\Who\Http\UI\Auth;

use Filament\Pages\SimplePage;
use Rimba\Who\Contracts\FaceVerifierContract;
use Rimba\Who\Contracts\PanelAccessResolverContract;

class VerifyFace extends SimplePage
{
    protected string $view = 'bites::auth.verify-face';

    public function faceMatched(): void
    {
        app(FaceVerifierContract::class)->recordVerification(auth()->user(), request()->ip(), request()->userAgent());
        $destination = app(PanelAccessResolverContract::class)->destinationFor(auth()->user());
        $this->redirect(filament()->getPanel($destination)->getUrl());
    }
}
