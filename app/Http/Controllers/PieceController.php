<?php

namespace App\Http\Controllers;

use App\Models\Piece;
use Illuminate\Http\Request;

class PieceController extends Controller
{
    /**
     * Liste des pièces (page admin).
     */
    public function index()
    {
        // 12 pièces par page pour la pagination
        $pieces = Piece::orderBy('reference')->paginate(12);

        return view('pieces.index', compact('pieces'));
    }

    /**
     * Formulaire de création.
     */
    public function create()
    {
        return view('pieces.create');
    }

    /**
     * Enregistrement d'une nouvelle pièce.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'reference'   => 'required|string|max:100|unique:pieces,reference',
            'nom'         => 'required|string|max:255',
            'description' => 'nullable|string',
            'prix'        => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            // image upload optionnelle : à ajouter plus tard si tu veux uploader
        ]);

        Piece::create($data);

        return redirect()
            ->route('pieces.index')
            ->with('success', 'Pièce créée avec succès.');
    }

    /**
     * Détail d'une pièce.
     */
    public function show(Piece $piece)
    {
        return view('pieces.show', compact('piece'));
    }

    /**
     * Formulaire d’édition.
     */
    public function edit(Piece $piece)
    {
        return view('pieces.edit', compact('piece'));
    }

    /**
     * Mise à jour d'une pièce.
     */
    public function update(Request $request, Piece $piece)
    {
        $data = $request->validate([
            'reference'   => 'required|string|max:100|unique:pieces,reference,' . $piece->id,
            'nom'         => 'required|string|max:255',
            'description' => 'nullable|string',
            'prix'        => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
        ]);

        $piece->update($data);

        return redirect()
            ->route('pieces.index')
            ->with('success', 'Pièce mise à jour.');
    }

    /**
     * Suppression d'une pièce.
     */
    public function destroy(Piece $piece)
    {
        $piece->delete();

        return redirect()
            ->route('pieces.index')
            ->with('success', 'Pièce supprimée.');
    }
}
