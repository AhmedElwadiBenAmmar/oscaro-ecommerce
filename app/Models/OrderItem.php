<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'piece_id',
        'nom', 'reference', 'prix', 'quantite',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
