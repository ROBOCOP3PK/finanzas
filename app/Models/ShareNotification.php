<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ShareNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'title',
        'message',
        'read',
        'read_at'
    ];

    protected $casts = [
        'read' => 'boolean',
        'read_at' => 'datetime'
    ];

    const TYPE_EXPENSE_REQUEST = 'expense_request';
    const TYPE_EXPENSE_APPROVED = 'expense_approved';
    const TYPE_EXPENSE_REJECTED = 'expense_rejected';
    const TYPE_SHARE_INVITATION = 'share_invitation';
    const TYPE_SHARE_REVOKED = 'share_revoked';

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('read', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Helpers
    public function markAsRead(): void
    {
        if (!$this->read) {
            $this->update([
                'read' => true,
                'read_at' => now()
            ]);
        }
    }

    public function isRead(): bool
    {
        return $this->read;
    }

    /**
     * Crear notificacion de solicitud de gasto
     */
    public static function createExpenseRequest(PendingExpense $expense, User $requester): self
    {
        return self::create([
            'user_id' => $expense->owner_id,
            'type' => self::TYPE_EXPENSE_REQUEST,
            'notifiable_type' => PendingExpense::class,
            'notifiable_id' => $expense->id,
            'title' => 'Nueva solicitud de gasto',
            'message' => "{$requester->name} solicita registrar: {$expense->concepto} - \${$expense->valor}"
        ]);
    }

    /**
     * Crear notificacion de gasto aprobado
     */
    public static function createExpenseApproved(PendingExpense $expense): self
    {
        return self::create([
            'user_id' => $expense->created_by,
            'type' => self::TYPE_EXPENSE_APPROVED,
            'notifiable_type' => PendingExpense::class,
            'notifiable_id' => $expense->id,
            'title' => 'Gasto aprobado',
            'message' => "Tu solicitud '{$expense->concepto}' fue aprobada"
        ]);
    }

    /**
     * Crear notificacion de gasto rechazado
     */
    public static function createExpenseRejected(PendingExpense $expense): self
    {
        $message = "Tu solicitud '{$expense->concepto}' fue rechazada";
        if ($expense->rejection_reason) {
            $message .= ": {$expense->rejection_reason}";
        }

        return self::create([
            'user_id' => $expense->created_by,
            'type' => self::TYPE_EXPENSE_REJECTED,
            'notifiable_type' => PendingExpense::class,
            'notifiable_id' => $expense->id,
            'title' => 'Gasto rechazado',
            'message' => $message
        ]);
    }

    /**
     * Crear notificacion de invitacion a compartir
     */
    public static function createShareInvitation(DataShare $share, User $owner): self
    {
        return self::create([
            'user_id' => $share->guest_id,
            'type' => self::TYPE_SHARE_INVITATION,
            'notifiable_type' => DataShare::class,
            'notifiable_id' => $share->id,
            'title' => 'Invitacion para ver datos',
            'message' => "{$owner->name} quiere compartir sus datos financieros contigo"
        ]);
    }

    /**
     * Crear notificacion de acceso revocado
     */
    public static function createShareRevoked(DataShare $share, User $owner): self
    {
        return self::create([
            'user_id' => $share->guest_id,
            'type' => self::TYPE_SHARE_REVOKED,
            'notifiable_type' => DataShare::class,
            'notifiable_id' => $share->id,
            'title' => 'Acceso revocado',
            'message' => "{$owner->name} ha revocado tu acceso a sus datos"
        ]);
    }
}
