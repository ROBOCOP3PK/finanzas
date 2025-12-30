<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'persona_secundaria_id',
        'porcentaje_persona_2',
        'dia_restablecimiento_servicios',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'porcentaje_persona_2' => 'decimal:2',
            'dia_restablecimiento_servicios' => 'integer',
        ];
    }

    // Relación: mi persona secundaria
    public function personaSecundaria(): BelongsTo
    {
        return $this->belongsTo(User::class, 'persona_secundaria_id');
    }

    // Relación: usuarios que me tienen como persona secundaria
    public function cuentasPrincipales(): HasMany
    {
        return $this->hasMany(User::class, 'persona_secundaria_id');
    }

    // Mis gastos (como dueño de la cuenta)
    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class);
    }

    // Mis abonos
    public function abonos(): HasMany
    {
        return $this->hasMany(Abono::class);
    }

    // Mis medios de pago
    public function mediosPago(): HasMany
    {
        return $this->hasMany(MedioPago::class);
    }

    // Mis categorías
    public function categorias(): HasMany
    {
        return $this->hasMany(Categoria::class);
    }

    // Mis plantillas
    public function plantillas(): HasMany
    {
        return $this->hasMany(Plantilla::class);
    }

    // Mis gastos recurrentes
    public function gastosRecurrentes(): HasMany
    {
        return $this->hasMany(GastoRecurrente::class);
    }

    // Mis conceptos frecuentes
    public function conceptosFrecuentes(): HasMany
    {
        return $this->hasMany(ConceptoFrecuente::class);
    }

    // Mis servicios
    public function servicios(): HasMany
    {
        return $this->hasMany(Servicio::class);
    }

    // ========================================
    // RELACIONES DE COMPARTICION DE DATOS
    // ========================================

    /**
     * Comparticion donde soy propietario (activa o pendiente)
     */
    public function myDataShare(): HasOne
    {
        return $this->hasOne(DataShare::class, 'owner_id')
            ->whereIn('status', [DataShare::STATUS_PENDING, DataShare::STATUS_ACCEPTED])
            ->latest();
    }

    /**
     * Todas mis comparticiones como propietario (historico)
     */
    public function dataSharesAsOwner(): HasMany
    {
        return $this->hasMany(DataShare::class, 'owner_id');
    }

    /**
     * Comparticiones donde soy invitado (activas)
     */
    public function accessibleDataShares(): HasMany
    {
        return $this->hasMany(DataShare::class, 'guest_id')
            ->where('status', DataShare::STATUS_ACCEPTED);
    }

    /**
     * Invitaciones pendientes para mi (como invitado)
     */
    public function pendingInvitations(): HasMany
    {
        return $this->hasMany(DataShare::class, 'guest_id')
            ->where('status', DataShare::STATUS_PENDING);
    }

    /**
     * Gastos pendientes que debo aprobar (como propietario)
     */
    public function pendingExpensesToApprove(): HasMany
    {
        return $this->hasMany(PendingExpense::class, 'owner_id')
            ->where('status', PendingExpense::STATUS_PENDING);
    }

    /**
     * Gastos pendientes que he creado (como invitado)
     */
    public function myPendingExpenses(): HasMany
    {
        return $this->hasMany(PendingExpense::class, 'created_by');
    }

    /**
     * Mis notificaciones de comparticion
     */
    public function shareNotifications(): HasMany
    {
        return $this->hasMany(ShareNotification::class);
    }

    /**
     * Contador de notificaciones sin leer
     */
    public function unreadShareNotificationsCount(): int
    {
        return $this->shareNotifications()->where('read', false)->count();
    }

    /**
     * Contador de gastos pendientes por aprobar
     */
    public function pendingExpensesCount(): int
    {
        return $this->pendingExpensesToApprove()->count();
    }

    // Calcular deuda de persona secundaria
    public function calcularDeudaPersona2(): float
    {
        // Gastos 100% de pareja
        $gastosPareja = $this->gastos()
            ->where('tipo', 'pareja')
            ->sum('valor');

        // Gastos compartidos × porcentaje
        $gastosCompartidos = $this->gastos()
            ->where('tipo', 'compartido')
            ->sum('valor');
        $porcionCompartida = $gastosCompartidos * ($this->porcentaje_persona_2 / 100);

        // Abonos recibidos
        $abonos = $this->abonos()->sum('valor');

        return round($gastosPareja + $porcionCompartida - $abonos, 2);
    }

    // Total gastado en el mes actual
    public function gastoMesActual(): float
    {
        return $this->gastos()
            ->whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->sum('valor');
    }
}
