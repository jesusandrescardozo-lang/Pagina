<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'cargo',
        'qr_token',
        'qr_activo',
        'google2fa_secret',
        'google2fa_enabled',
        'shelter_id',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'qr_activo' => 'boolean',
        'google2fa_enabled' => 'boolean',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::needsRehash($value) ? bcrypt($value) : $value;
    }

    public function regenerateQrToken(): string
    {
        $this->qr_token = 'SEC-AUTH-' . strtoupper(\Illuminate\Support\Str::random(24));
        $this->qr_activo = true;
        $this->save();
        return $this->qr_token;
    }

    /**
     * Obtiene o genera la clave secreta de Google Authenticator para el usuario.
     */
    public function getGoogle2faSecret(): string
    {
        if (empty($this->google2fa_secret)) {
            $this->google2fa_secret = \App\Services\GoogleAuthenticatorService::generateSecret();
            $this->google2fa_enabled = true;
            $this->save();
        }
        return $this->google2fa_secret;
    }

    /**
     * Indica si el usuario ya tiene una clave secreta 2FA configurada
     * (sin generarla: es solo una consulta para la vista).
     */
    public function hasGoogle2faSecret(): bool
    {
        return !empty($this->google2fa_secret);
    }

    /**
     * Obtiene el URI estándar otpauth:// para vincular en la app Google Authenticator.
     */
    public function getGoogle2faOtpAuthUrl(): string
    {
        $secret = $this->getGoogle2faSecret();
        return \App\Services\GoogleAuthenticatorService::getOtpAuthUrl('NutriLnk', $this->email, $secret);
    }

    /**
     * Genera el código QR del 2FA como SVG local (el secreto no sale del servidor).
     */
    public function getGoogle2faQrSvg(int $size = 200): string
    {
        return \App\Services\GoogleAuthenticatorService::getQrCodeSvg($this->getGoogle2faOtpAuthUrl(), $size);
    }

    /**
     * Retorna el código de 6 dígitos actual generado para este usuario específico.
     */
    public function getCurrentGoogle2faCode(): string
    {
        return \App\Services\GoogleAuthenticatorService::getCode($this->getGoogle2faSecret());
    }

    public function shelter()
    {
        return $this->belongsTo(Shelter::class, 'shelter_id');
    }

    public function isAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public function isCoordinador(): bool
    {
        return in_array($this->rol, ['admin', 'coordinador_refugio']);
    }

    public function isAlmacenista(): bool
    {
        return in_array($this->rol, ['admin', 'almacenista']);
    }

    public function isAuditor(): bool
    {
        return in_array($this->rol, ['admin', 'auditor']);
    }

    public function canAccessShelters(): bool
    {
        return Gate::forUser($this)->allows('manage-shelters');
    }

    public function canAccessSupplies(): bool
    {
        return Gate::forUser($this)->allows('access-supplies');
    }

    public function canManageSupplies(): bool
    {
        return Gate::forUser($this)->allows('manage-supplies');
    }

    public function canAccessMovements(): bool
    {
        return Gate::forUser($this)->allows('access-movements');
    }

    public function canManageKardex(): bool
    {
        return Gate::forUser($this)->allows('manage-kardex');
    }

    public function canAccessTransfers(): bool
    {
        return Gate::forUser($this)->allows('manage-transfers');
    }

    public function canAccessCarnets(): bool
    {
        return Gate::forUser($this)->allows('manage-carnets');
    }

    public function canAccessAudit(): bool
    {
        return Gate::forUser($this)->allows('view-audit');
    }

    public function canAccessReports(): bool
    {
        return Gate::forUser($this)->allows('access-reports');
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class, 'user_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }
}
