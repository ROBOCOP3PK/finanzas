<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingExpense extends Model
{
    protected $fillable = [
        'data_share_id',
        'created_by',
        'owner_id',
        'fecha',
        'medio_pago_id',
        'categoria_id',
        'concepto',
        'valor',
        'tipo',
        'status',
        'rejection_reason',
        'resulting_gasto_id',
        'decided_at'
    ];

    protected $casts = [
        'fecha' => 'date',
        'valor' => 'decimal:2',
        'decided_at' => 'datetime'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    const TIPO_PERSONAL = 'personal';
    const TIPO_PAREJA = 'pareja';
    const TIPO_COMPARTIDO = 'compartido';

    // Relaciones
    public function dataShare(): BelongsTo
    {
        return $this->belongsTo(DataShare::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function medioPago(): BelongsTo
    {
        return $this->belongsTo(MedioPago::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function resultingGasto(): BelongsTo
    {
        return $this->belongsTo(Gasto::class, 'resulting_gasto_id');
    }

    public function notifications(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(ShareNotification::class, 'notifiable');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeForOwner($query, $ownerId)
    {
        return $query->where('owner_id', $ownerId);
    }

    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    // Helpers
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Aprobar la solicitud y crear el gasto
     */
    public function approve(): Gasto
    {
        $gasto = Gasto::create([
            'user_id' => $this->owner_id,
            'registrado_por' => $this->created_by,
            'fecha' => $this->fecha,
            'medio_pago_id' => $this->medio_pago_id,
            'categoria_id' => $this->categoria_id,
            'concepto' => $this->concepto,
            'valor' => $this->valor,
            'tipo' => $this->tipo
        ]);

        $this->update([
            'status' => self::STATUS_APPROVED,
            'resulting_gasto_id' => $gasto->id,
            'decided_at' => now()
        ]);

        return $gasto;
    }

    /**
     * Rechazar la solicitud
     */
    public function reject(?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'decided_at' => now()
        ]);
    }
}
