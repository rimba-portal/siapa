<?php

declare(strict_types=1);

namespace Rimba\Who\Models;

use Illuminate\Database\Eloquent\Model;

final class SecuritySetting extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }
}
