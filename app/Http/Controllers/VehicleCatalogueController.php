<?php

namespace App\Http\Controllers;

use App\Models\Piece;
use App\Models\Category;
use Illuminate\Http\Request;

class VehicleCatalogueController extends Controller
{
    /**
     * Affiche le catalogue des pièces (front office).
     */
    public function index(Request $request)
    {
        // Si une référence exacte est trouvée, on redirige directement vers la fiche produit
        if ($term = $request->input('q')) {
            $term = trim($term);

            $pieceExact = Piece::where('reference', $term)->first();

            if ($pieceExact) {
                return redirect()->route('produits.show', $pieceExact);
            }
        }

        // Base query avec relations utiles
        $query = Piece::query()
            ->with(['category', 'compatibleEngines'])
            ->where('stock', '>', 0);

        /*
         |---------------------------------------------------------
         | 1. Filtre compatibilité véhicule (sélection moteur)
         |---------------------------------------------------------
         */
        if ($engineId = session('selected_engine_id')) {
            $query->whereHas('compatibleEngines', function ($q) use ($engineId) {
                $q->where('vehicle_engines.id', $engineId);
            });
        }

        /*
         |---------------------------------------------------------
         | 2. Recherche texte simple (fallback si pas de référence exacte)
         |---------------------------------------------------------
         */
        if ($term = $request->input('q')) {
            $query->where(function ($q) use ($term) {
                $q->where('nom', 'like', "%{$term}%")
                  ->orWhere('reference', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            });
        }

        /*
         |---------------------------------------------------------
         | 3. Filtres catalogue (catégorie, marque, prix, côté, position)
         |---------------------------------------------------------
         */
        if ($categoryId = $request->input('category')) {
            $query->where('category_id', $categoryId);
        }

        if ($brand = $request->input('brand')) {
            $query->where('brand', $brand);
        }

        if ($side = $request->input('side')) { // left, right, both...
            $query->where('side', $side);
        }

        if ($position = $request->input('position')) { // front, rear...
            $query->where('position', $position);
        }

        if ($minPrice = $request->input('min_price')) {
            $query->where('prix', '>=', (float) $minPrice);
        }

        if ($maxPrice = $request->input('max_price')) {
            $query->where('prix', '<=', (float) $maxPrice);
        }

        /*
         |---------------------------------------------------------
         | 4. Tri (par défaut : pertinence / nouveauté)
         |---------------------------------------------------------
         */
        switch ($request->input('sort')) {
            case 'price_asc':
                $query->orderBy('prix', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('prix', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('nom', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('nom', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        /*
         |---------------------------------------------------------
         | 5. Pagination
         |---------------------------------------------------------
         */
        $pieces = $query->paginate(12)->withQueryString();

        // Données pour les filtres (sidebar)
        $categories = Category::orderBy('nom')->get();
        $brands     = Piece::select('brand')->distinct()->orderBy('brand')->pluck('brand');

        $selectedEngineId = session('selected_engine_id');

        return view('pieces.catalogue', [
            'pieces'           => $pieces,
            'categories'       => $categories,
            'brands'           => $brands,
            'selectedEngineId' => $selectedEngineId,
            'filters'          => [
                'q'         => $request->input('q'),
                'category'  => $request->input('category'),
                'brand'     => $request->input('brand'),
                'side'      => $request->input('side'),
                'position'  => $request->input('position'),
                'min_price' => $request->input('min_price'),
                'max_price' => $request->input('max_price'),
                'sort'      => $request->input('sort'),
            ],
        ]);
    }

    /**
     * Affiche le détail d'une pièce.
     */
    public function show(Piece $piece)
    {
        // Pièces similaires : même catégorie, exclure la pièce courante
        $similarPieces = Piece::where('category_id', $piece->category_id)
            ->where('id', '!=', $piece->id)
            ->orderBy('prix', 'asc')
            ->take(6)
            ->get();

            return view('produits.show', [
                'piece'         => $piece,
                'similarPieces' => $similarPieces,
                'complementary' => collect(), // temporaire si tu n'as pas encore le service
            ]);
            
    }
}
