<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleEngine extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_model_id',
        'name',        // 1.6 HDi, 1.5 dCi, ...
        'displacement',// 1598, 1498 ...
        'power_hp',    // 110, 130 ...
        'fuel_type',   // diesel, essence
    ];

    public function model()
    {
        return $this->belongsTo(VehicleModel::class);
    }

    public function pieces()
    {
        return $this->belongsToMany(Piece::class, 'vehicle_piece');
    }
}
