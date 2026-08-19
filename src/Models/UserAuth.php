<?php

declare(strict_types=1);

namespace Rimba\Who\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Rimba\Who\Enums\SecurityLevel;

class UserAuth extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'totp_secret' => 'encrypted',
            'totp_recovery_codes' => 'encrypted:array',

            'face_descriptor' => 'encrypted:array',

            'setup_completed' => 'boolean',

            'last_login_at' => 'datetime',
            'last_face_auth_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            config('auth.providers.users.model')
        );
    }

    public function hasTotp(): bool
    {
        return filled($this->totp_secret);
    }

    public function hasValidFaceAuth(): bool
    {
        if (! $this->last_face_auth_at) {
            return false;
        }

        return $this->last_face_auth_at->greaterThan(
            now()->subMinutes(
                config(
                    'bites_auth.security.face_auth_timeout_minutes',
                    10
                )
            )
        );
    }

    public function markSetupCompleted(): void
    {
        $this->update([
            'setup_completed' => true,
        ]);
    }

    public function markSetupIncomplete(): void
    {
        $this->update([
            'setup_completed' => false,
        ]);
    }

    public function securityLevel(): SecurityLevel
    {
        if (! auth()->check()) {
            return SecurityLevel::Public;
        }

        if (! $this->hasValidFaceAuth()) {
            return SecurityLevel::Authenticated;
        }

        return SecurityLevel::FaceVerified;
    }
}
