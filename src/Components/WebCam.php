<?php

declare(strict_types=1);

namespace Rimba\Who\Components;

use Filament\Forms\Components\Field;
use Illuminate\Support\Facades\Storage;

class WebCam extends Field
{
    protected string $view = 'bites::web-cam';

    public function getPhotoUrl(): ?string
    {
        $state = $this->getState();

        if (blank($state)) {
            return null;
        }

        return Storage::disk('public')->url($state);
    }
}
