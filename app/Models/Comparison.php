<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comparison extends Model
{
    use HasFactory;

    /**
     * Table associée.
     *
     * @var string
     */
    protected $table = 'comparisons';

    /**
     * Attributs remplissables.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'piece_id',
        'position',
    ];

    /**
     * Casts.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'position' => 'integer',
    ];

    /**
     * Utilisateur propriétaire de cette ligne de comparaison.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Pièce associée à cette entrée de comparaison.
     */
    public function piece(): BelongsTo
    {
        return $this->belongsTo(Piece::class);
    }

    /**
     * Scope : comparaisons d’un utilisateur.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope : comparaisons pour une pièce.
     */
    public function scopeForPiece($query, int $pieceId)
    {
        return $query->where('piece_id', $pieceId);
    }

    /**
     * Scope : ordonner par position.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }
}
