<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ComparisonController extends Controller
{
    const MAX_PRODUCTS = 4;

    /**
     * Affiche la page de comparaison.
     */
    public function index()
    {
        // Récupère les entrées de comparaison de l'utilisateur avec la pièce associée
        $products = auth()->user()->comparisons()
            ->with('product')      // relation product() dans le modèle Comparison
            ->get()
            ->pluck('product');    // on ne garde que la collection de Piece

        return view('comparison.index', compact('products'));
    }

    /**
     * Ajoute un produit au comparateur.
     */
    public function add(Request $request)
    {
        // Validation simple
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
        ]);

        // Limite de produits
        $count = auth()->user()->comparisons()->count();
        if ($count >= self::MAX_PRODUCTS) {
            return back()->with('error', 'Vous pouvez comparer maximum 4 produits');
        }

        // Ajout si pas déjà présent (en utilisant piece_id dans la table)
        auth()->user()->comparisons()->firstOrCreate(
            ['piece_id' => $data['product_id']]
        );

        return back()->with('success', 'Produit ajouté au comparateur');
    }

    /**
     * Retire un produit du comparateur.
     */
    public function remove($id)
    {
        auth()->user()->comparisons()
            ->where('piece_id', $id)
            ->delete();

        return back();
    }

    /**
     * Vide complètement le comparateur.
     */
    public function clear()
    {
        auth()->user()->comparisons()->delete();

        return back()->with('success', 'Comparateur vidé');
    }
}
