<?php

declare(strict_types=1);

namespace Rimba\Who\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;

#[Unguarded]
final class SecuritySetting extends Model
{
    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }
}
