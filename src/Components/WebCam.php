<?php

declare(strict_types=1);

namespace Rimba\Who\Components;

use Filament\Forms\Components\Field;

class Webcam extends Field
{
    // Point to your custom blade view
    protected string $view = 'bites::webcam';
}
