<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Piece;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function create()
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')
                ->with('error', 'Votre panier est vide.');
        }

        $total = collect($cart)->sum(fn ($item) => $item['prix'] * $item['quantite']);

        return view('checkout.create', compact('cart', 'total'));
    }

    public function store(Request $request)
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')
                ->with('error', 'Votre panier est vide.');
        }

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email',
            'phone'       => 'nullable|string|max:50',
            'address'     => 'required|string|max:255',
            'city'        => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
        ]);

        $total = collect($cart)->sum(fn ($item) => $item['prix'] * $item['quantite']);

        DB::transaction(function () use ($data, $cart, $total) {
            // 1) Créer la commande
            $order = Order::create(array_merge($data, [
                'total'  => $total,
                'status' => 'pending',
            ]));

            // 2) Créer les lignes + vérifier stock et décrémenter
            foreach ($cart as $item) {
                $piece = Piece::findOrFail($item['id']);

                if ($piece->stock < $item['quantite']) {
                    throw new \Exception("Stock insuffisant pour {$piece->nom}");
                }

                OrderItem::create([
                    'order_id'  => $order->id,
                    'piece_id'  => $piece->id,
                    'nom'       => $piece->nom,
                    'reference' => $piece->reference,
                    'prix'      => $piece->prix,
                    'quantite'  => $item['quantite'],
                ]);

                $piece->decrement('stock', $item['quantite']);
            }
        });

        // 3) Vider le panier
        session()->forget('cart');

        return redirect()->route('produits.index')
            ->with('success', 'Votre commande a été enregistrée.');
    }
}
