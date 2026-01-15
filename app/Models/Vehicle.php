<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'make',          // marque
        'model',         // modèle
        'year',          // année
        'engine',        // motorisation
        'fuel_type',     // type carburant
        'vin',           // VIN (optionnel)
        'plate_number',  // immatriculation (optionnel)
        'user_id',       // propriétaire dans "mon garage" (nullable)
    ];

    /**
     * Propriétaire du véhicule (optionnel, pour "mon garage").
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Pièces compatibles avec ce véhicule.
     * Pivot recommandé : piece_vehicle (piece_id, vehicle_id).
     */
    public function pieces()
    {
        return $this->belongsToMany(Piece::class, 'piece_vehicle')
            ->withTimestamps();
    }

    /**
     * Interactions utilisateur liées à ce véhicule
     * (si tu ajoutes vehicle_id dans user_product_interactions).
     */
    public function interactions()
    {
        return $this->hasMany(UserProductInteraction::class);
    }
    public function compatiblePieces()
{
    return $this->belongsToMany(Piece::class, 'piece_vehicle_compatibility')
                ->withPivot(['verified', 'notes'])
                ->withTimestamps();
}

}
