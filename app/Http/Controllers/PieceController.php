<?php

namespace App\Http\Controllers;

use App\Models\Piece;
use Illuminate\Http\Request;

class PieceController extends Controller
{
    public function index()
    {
        $pieces = Piece::latest()->paginate(12);
    
        return view('pieces.index', compact('pieces'));
    }
    


    public function create()
    {
        return view('pieces.create');
    }
    
    public function store(Request $request)
{
    $data = $request->validate([
        'reference' => 'required|string|max:100',
        'nom'       => 'required|string|max:255',
        'description' => 'nullable|string',
        'prix'      => 'required|numeric|min:0',
        'stock'     => 'required|integer|min:0',
        'categorie' => 'nullable|string|max:100',
    ]);

    Piece::create($data);

    return redirect()
        ->route('pieces.index')
        ->with('success', 'Pièce créée avec succès.');
}


    public function show(Piece $piece)
    {
        return view('pieces.show', compact('piece'));
    }

    public function edit(Piece $piece)
    {
        return view('pieces.edit', compact('piece'));
    }

    public function update(Request $request, Piece $piece)
    {
        $data = $request->validate([
            'reference'   => 'required|string|max:100|unique:pieces,reference,' . $piece->id,
            'nom'         => 'required|string|max:255',
            'description' => 'nullable|string',
            'prix'        => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'categorie'   => 'nullable|string|max:100',
        ]);

        $piece->update($data);

        return redirect()->route('pieces.index')
            ->with('success', 'Pièce mise à jour.');
    }

    public function destroy(Piece $piece)
    {
        $piece->delete();

        return redirect()->route('pieces.index')
            ->with('success', 'Pièce supprimée.');
    }
}
