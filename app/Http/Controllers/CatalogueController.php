<?php

namespace App\Http\Controllers;

use App\Models\Piece;

class CatalogueController extends Controller
{
    public function index()
    {
        $pieces = Piece::latest()->paginate(12);

        return view('produits.index', compact('pieces'));
    }

    public function show(Piece $piece)
    {
        return view('produits.show', compact('piece'));
    }
}
