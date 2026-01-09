<?php

namespace App\Http\Controllers;

use App\Models\Piece;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);

        // total HT simple
        $total = collect($cart)->sum(fn ($item) => $item['prix'] * $item['quantite']);

        return view('panier.index', compact('cart', 'total'));
    }

    public function add(Request $request, Piece $piece)
    {
        $cart = session('cart', []);

        if (isset($cart[$piece->id])) {
            $cart[$piece->id]['quantite']++;
        } else {
            $cart[$piece->id] = [
                'id'       => $piece->id,
                'nom'      => $piece->nom,
                'reference'=> $piece->reference,
                'prix'     => $piece->prix,
                'quantite' => 1,
            ];
        }

        session(['cart' => $cart]);

        return back()->with('success', 'Pièce ajoutée au panier.');
    }

    public function update(Request $request, Piece $piece)
    {
        $request->validate([
            'quantite' => 'required|integer|min:1',
        ]);

        $cart = session('cart', []);

        if (isset($cart[$piece->id])) {
            $cart[$piece->id]['quantite'] = (int) $request->quantite;
            session(['cart' => $cart]);
        }

        return back()->with('success', 'Quantité mise à jour.');
    }

    public function remove(Piece $piece)
    {
        $cart = session('cart', []);

        unset($cart[$piece->id]);

        session(['cart' => $cart]);

        return back()->with('success', 'Pièce retirée du panier.');
    }

    public function clear()
    {
        session()->forget('cart');

        return back()->with('success', 'Panier vidé.');
    }
}
