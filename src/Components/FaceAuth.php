<?php

declare(strict_types=1);

namespace Rimba\Who\Components;

use Closure;
use Filament\Forms\Components\Field;

class FaceAuth extends Field
{
    protected string $view = 'bites::face-auth';

    protected string|Closure|null $staffNo = null;

    public function staffNo(string|Closure|null $staffNo): static
    {
        $this->staffNo = $staffNo;

        return $this;
    }

    public function getStaffNo(): ?string
    {
        return $this->evaluate($this->staffNo);
    }
}
