<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VerificationCode extends Model
{
    protected $fillable = [
        'email',
        'code',
        'type',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Generar un nuevo codigo de verificacion
     */
    public static function generateFor(string $email, string $type = 'register', int $expiresInMinutes = 10): self
    {
        // Invalidar codigos anteriores del mismo tipo
        self::where('email', $email)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->delete();

        // Crear nuevo codigo de 6 digitos
        return self::create([
            'email' => $email,
            'code' => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'type' => $type,
            'expires_at' => now()->addMinutes($expiresInMinutes),
        ]);
    }

    /**
     * Verificar un codigo
     */
    public static function verify(string $email, string $code, string $type = 'register'): ?self
    {
        $verification = self::where('email', $email)
            ->where('code', $code)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($verification) {
            $verification->update(['verified_at' => now()]);
        }

        return $verification;
    }

    /**
     * Verificar si un email tiene un codigo verificado reciente
     */
    public static function isVerified(string $email, string $type = 'register'): bool
    {
        return self::where('email', $email)
            ->where('type', $type)
            ->whereNotNull('verified_at')
            ->where('verified_at', '>', now()->subMinutes(30)) // Valido por 30 minutos despues de verificar
            ->exists();
    }

    /**
     * Limpiar codigos expirados
     */
    public static function cleanExpired(): int
    {
        return self::where('expires_at', '<', now())
            ->orWhere(function ($query) {
                $query->whereNotNull('verified_at')
                    ->where('verified_at', '<', now()->subHours(1));
            })
            ->delete();
    }

    /**
     * Verificar si el codigo ha expirado
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Verificar si el codigo ya fue usado
     */
    public function isUsed(): bool
    {
        return $this->verified_at !== null;
    }
}
