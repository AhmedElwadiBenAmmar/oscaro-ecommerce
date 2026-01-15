<?php

namespace App\Http\Controllers;

use App\Models\Piece;
use App\Models\Category;
use App\Models\Vehicle;                    // <-- ajouter
use App\Services\RecommendationService;    // <-- ajouter
use Illuminate\Http\Request;

class CatalogueController extends Controller
{
    /**
     * Affiche le catalogue des pièces (front office).
     */
    public function index(Request $request)
    {
        // 0. Recherche par référence exacte
        if ($term = $request->input('q')) {
            $term = trim($term);
            $term = preg_replace('/^réf\.?\s*/i', '', $term);

            $pieceExact = Piece::where('reference', $term)->first();

            if ($pieceExact) {
                return redirect()->route('produits.show', $pieceExact);
            }
        }

        // 1. Requête catalogue classique
        $query = Piece::query()
            ->with(['category', 'compatibleEngines'])
            ->where('stock', '>', 0);

        // 1. Filtre compatibilité moteur
        if ($engineId = session('selected_engine_id')) {
            $query->whereHas('compatibleEngines', function ($q) use ($engineId) {
                $q->where('vehicle_engines.id', $engineId);
            });
        }

        // 2. Recherche texte
        if ($term = $request->input('q')) {
            $query->where(function ($q) use ($term) {
                $q->where('nom', 'like', "%{$term}%")
                  ->orWhere('reference', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            });
        }

        // 3. Filtres catalogue
        if ($categoryId = $request->input('category')) {
            $query->where('category_id', $categoryId);
        }

        if ($side = $request->input('side')) {
            $query->where('side', $side);
        }

        if ($position = $request->input('position')) {
            $query->where('position', $position);
        }

        if ($minPrice = $request->input('min_price')) {
            $query->where('prix', '>=', (float) $minPrice);
        }

        if ($maxPrice = $request->input('max_price')) {
            $query->where('prix', '<=', (float) $maxPrice);
        }

        if ($vehicleId = session('selected_vehicle_id')) {
            $query->whereHas('compatibleVehicles', function ($q) use ($vehicleId) {
                $q->where('vehicles.id', $vehicleId);
            });
        }

        // 4. Tri
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

        // 5. Pagination
        $pieces = $query->paginate(12)->withQueryString();

        $categories = Category::orderBy('nom')->get();
        $brands = collect();
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

    public function show(Piece $piece)
    {
        $similarPieces = Piece::where('category_id', $piece->category_id)
            ->where('id', '!=', $piece->id)
            ->orderBy('prix', 'asc')
            ->take(6)
            ->get();

        // Recos complémentaires via service
        $vehicle = session('current_vehicle_id')
            ? Vehicle::find(session('current_vehicle_id'))
            : null;

        $complementary = app(RecommendationService::class)
            ->getComplementaryProducts($piece, 6, $vehicle);

            return view('produits.show', compact('piece', 'similarPieces', 'complementary'));

    }
}

