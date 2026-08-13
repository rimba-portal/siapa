<?php

declare(strict_types=1);

namespace Rimba\Who\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AuthenticationAttempt extends Model
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
