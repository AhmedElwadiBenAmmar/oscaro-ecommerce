<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Piece extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'nom',
        'description',
        'prix',
        'brand_id',
        'stock',
        'image',        // ajouté
        'category_id',  // remplace 'categorie'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
     /**
     * Articles de commande associés à cette pièce.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'piece_id');
    }
    public function images()
{
    return $this->hasMany(ProductImage::class);
}
public function compatibleEngines()
{
    return $this->belongsToMany(VehicleEngine::class, 'vehicle_piece');
}


}

