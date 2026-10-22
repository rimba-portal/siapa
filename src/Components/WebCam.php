<?php

declare(strict_types=1);

namespace Rimba\Who\Components;

use Filament\Forms\Components\Field;
use Illuminate\Support\Facades\Storage;

class WebCam extends Field
{
    protected string $view = 'bites::webcam';

    public function getPhotoUrl(): ?string
    {
        $state = $this->getState();

        if (! is_string($state) || blank($state)) {
            return null;
        }

        if (str_starts_with($state, 'data:image/')) {
            return $state;
        }

        if (
            str_starts_with($state, 'http://')
            || str_starts_with($state, 'https://')
        ) {
            return $state;
        }

        return Storage::disk('public')->url($state);
    }
}
