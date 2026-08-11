<?php

declare(strict_types=1);

namespace Rimba\Who\Models;

use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Rimba\Who\Enums\SecurityLevel;

#[Hidden([
    'two_factor_secret',
    'two_factor_recovery_codes',
    'face_descriptor',
])]
class UserAuth extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
            'face_descriptor' => 'encrypted:array',
            'last_login_at' => 'datetime',
            'last_face_auth_at' => 'datetime',
            'setup_completed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function hasTwoFactor(): bool
    {
        return $this->two_factor_confirmed_at !== null;
    }

    public function hasValidFaceAuth(): bool
    {
        return $this->last_face_auth_at?->greaterThan(
            now()->subMinutes((int) config('siapa.security.face_auth_timeout_minutes', 10)),
        ) ?? false;
    }

    public function securityLevel(): SecurityLevel
    {
        if (! auth()->check()) {
            return SecurityLevel::Public;
        }

        if (! $this->hasTwoFactor()) {
            return SecurityLevel::Authenticated;
        }

        if (! $this->hasValidFaceAuth()) {
            return SecurityLevel::TwoFactorVerified;
        }

        return SecurityLevel::FaceVerified;
    }
}
