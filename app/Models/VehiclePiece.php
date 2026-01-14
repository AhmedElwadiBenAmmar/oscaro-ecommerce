<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehiclePiece extends Model
{
    use HasFactory;

    protected $table = 'vehicle_piece';

    protected $fillable = [
        'vehicle_engine_id',
        'piece_id',
    ];
}
