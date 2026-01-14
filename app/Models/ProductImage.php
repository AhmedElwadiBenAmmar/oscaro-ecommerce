<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = [
        'piece_id',      // FK vers la pièce
        'path',          // chemin ou URL de l'image
        'is_primary',    // bool : image principale
        'position',      // ordre d'affichage
        'alt_text',      // texte alternatif (SEO/accessibilité)
    ];

    /**
     * Pièce associée à cette image.
     */
    public function piece()
    {
        return $this->belongsTo(Piece::class);
    }
}
