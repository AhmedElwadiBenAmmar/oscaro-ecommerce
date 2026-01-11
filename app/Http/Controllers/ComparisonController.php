<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// app/Http/Controllers/ComparisonController.php
class ComparisonController extends Controller
{
    const MAX_PRODUCTS = 4;

    public function index() {
        $products = auth()->user()->comparisons()
            ->with('product')
            ->get()
            ->pluck('product');
        
        return view('comparison.index', compact('products'));
    }

    public function add(Request $request) {
        $count = auth()->user()->comparisons()->count();
        
        if ($count >= self::MAX_PRODUCTS) {
            return back()->with('error', 'Vous pouvez comparer maximum 4 produits');
        }

        auth()->user()->comparisons()->firstOrCreate(
            ['product_id' => $request->product_id],
            ['added_at' => now()]
        );

        return back()->with('success', 'Produit ajouté au comparateur');
    }

    public function remove($id) {
        auth()->user()->comparisons()->where('product_id', $id)->delete();
        return back();
    }

    public function clear() {
        auth()->user()->comparisons()->delete();
        return back()->with('success', 'Comparateur vidé');
    }
}

