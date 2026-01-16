<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comparison extends Model
{
    use HasFactory;

    protected $table = 'comparisons';

    protected $fillable = [
        'user_id',
        'piece_id',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // la pièce associée
    public function product(): BelongsTo
    {
        return $this->belongsTo(Piece::class, 'piece_id');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForPiece($query, int $pieceId)
    {
        return $query->where('piece_id', $pieceId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }
}
