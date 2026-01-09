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
        // 1) Si stock à 0 → on bloque
        if ($piece->stock <= 0) {
            return back()->with('error', 'Cette pièce est en rupture de stock.');
        }

        $cart = session('cart', []);

        // 2) Quantité actuelle dans le panier
        $currentQty = isset($cart[$piece->id]) ? $cart[$piece->id]['quantite'] : 0;
        $newQty     = $currentQty + 1;

        // 3) Ne jamais dépasser le stock réel
        if ($newQty > $piece->stock) {
            return back()->with('error', 'Stock insuffisant pour ajouter un exemplaire de plus.');
        }

        // 4) Logique d’ajout/mise à jour inchangée
        if (isset($cart[$piece->id])) {
            $cart[$piece->id]['quantite'] = $newQty;
        } else {
            $cart[$piece->id] = [
                'id'        => $piece->id,
                'nom'       => $piece->nom,
                'reference' => $piece->reference,
                'prix'      => $piece->prix,
                'quantite'  => 1,
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

        if (! isset($cart[$piece->id])) {
            return back()->with('error', 'Cette pièce n’est pas dans votre panier.');
        }

        $requestedQty = (int) $request->quantite;

        // 1) Vérifier que la quantité demandée ne dépasse pas le stock
        if ($requestedQty > $piece->stock) {
            return back()->with(
                'error',
                'Stock insuffisant, quantité maximale disponible : ' . $piece->stock
            );
        }

        // 2) Mise à jour de la quantité
        $cart[$piece->id]['quantite'] = $requestedQty;
        session(['cart' => $cart]);

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
