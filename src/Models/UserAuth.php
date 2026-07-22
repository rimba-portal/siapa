<?php

declare(strict_types=1);

namespace Rimba\Who\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'two_factor_secret',
    'two_factor_recovery_codes',
    'two_factor_confirmed_at',
    'face_descriptor',
    'setup_completed',
    'is_admin',
    'is_staff',
    'last_login',
    'last_face_auth',
])]
#[Table(name: 'user_auth')]
class UserAuth extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    protected function casts(): array
    {
        return [
            'two_factor_confirmed_at' => 'datetime',
            'last_login' => 'datetime',
            'last_face_auth' => 'datetime',
            'setup_completed' => 'boolean',
            'is_admin' => 'boolean',
            'is_staff' => 'boolean',
        ];
    }
}
