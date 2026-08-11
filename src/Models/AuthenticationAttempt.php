<?php

declare(strict_types=1);

namespace Rimba\Who\Models;

use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Hidden([
    'two_factor_secret',
    'two_factor_recovery_codes',
    'face_descriptor',
])]
class AuthenticationAttempt extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['success' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}
