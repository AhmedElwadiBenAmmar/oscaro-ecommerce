<?php

namespace App\Http\Controllers;

use App\Models\Piece;
use App\Models\User;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\UserProductInteraction;
use App\Models\Vehicle;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecommendationController extends Controller
{
    protected RecommendationService $recommendationService;

    public function __construct(RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    /**
     * Afficher les recommandations personnalisées pour l'utilisateur.
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Vous devez être connecté pour voir vos recommandations.');
        }

        $personalizedRecommendations = $this->recommendationService
            ->getPersonalizedRecommendations($user, 12);

        $popularProducts = $this->recommendationService
            ->getPopularProducts(8);

        $recentlyViewed = $this->recommendationService
            ->getRecentlyViewedProducts($user, 6);

        $trendingProducts = $this->recommendationService
            ->getTrendingProducts(6);

        return view('recommendations.index', compact(
            'personalizedRecommendations',
            'popularProducts',
            'recentlyViewed',
            'trendingProducts'
        ));
    }

    /**
     * Recommandations pour une pièce spécifique.
     */
    public function forProduct($pieceId, Request $request)
    {
        $piece = Piece::findOrFail($pieceId);
        $user = Auth::user();

        $similarProducts = $this->recommendationService
            ->getSimilarProducts($piece, 8);

        $frequentlyBoughtTogether = $this->recommendationService
            ->getFrequentlyBoughtTogether($piece, 4);

        $complementaryProducts = $this->recommendationService
            ->getComplementaryProducts($piece, 6);

        if ($request->ajax()) {
            return response()->json([
                'similar' => $similarProducts,
                'frequently_bought_together' => $frequentlyBoughtTogether,
                'complementary' => $complementaryProducts,
            ]);
        }

        return view('recommendations.product', compact(
            'piece',
            'similarProducts',
            'frequentlyBoughtTogether',
            'complementaryProducts'
        ));
    }

    /**
     * Recommandations par catégorie.
     */
    public function forCategory($categorySlug)
    {
        $category = Category::where('slug', $categorySlug)->firstOrFail();

        $topProducts = Piece::where('category_id', $category->id)
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->withCount('orderItems')
            ->orderBy('order_items_count', 'desc')
            ->limit(12)
            ->get();

        $newProducts = Piece::where('category_id', $category->id)
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        return view('recommendations.category', compact(
            'category',
            'topProducts',
            'newProducts'
        ));
    }

    /**
     * API : recommandations rapides (widgets).
     */
    public function quick(Request $request)
    {
        $type = $request->input('type', 'popular');
        $limit = (int) $request->input('limit', 6);
        $user = Auth::user();

        $recommendations = collect();

        switch ($type) {
            case 'personalized':
                if ($user) {
                    $recommendations = $this->recommendationService
                        ->getPersonalizedRecommendations($user, $limit);
                }
                break;

            case 'popular':
                $recommendations = $this->recommendationService
                    ->getPopularProducts($limit);
                break;

            case 'trending':
                $recommendations = $this->recommendationService
                    ->getTrendingProducts($limit);
                break;

            case 'recent':
                if ($user) {
                    $recommendations = $this->recommendationService
                        ->getRecentlyViewedProducts($user, $limit);
                }
                break;

            case 'new':
                $recommendations = Piece::where('is_active', true)
                    ->where('stock', '>', 0)
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get();
                break;

            default:
                return response()->json(['error' => 'Type invalide'], 400);
        }

        return response()->json([
            'type' => $type,
            'count' => $recommendations->count(),
            'products' => $recommendations,
        ]);
    }

    /**
     * Recommandations pour le panier.
     */
    public function forCart(Request $request)
    {
        $cartItems = $request->input('cart_items', []); // IDs de pièces

        if (empty($cartItems)) {
            return response()->json([
                'recommendations' => [],
                'message' => 'Panier vide',
            ]);
        }

        $pieces = Piece::whereIn('id', $cartItems)->get();
        $recommendations = collect();

        foreach ($pieces as $piece) {
            $complementary = $this->recommendationService
                ->getComplementaryProducts($piece, 3);

            $recommendations = $recommendations->merge($complementary);
        }

        $recommendations = $recommendations->unique('id')
            ->whereNotIn('id', $cartItems)
            ->take(6)
            ->values();

        return response()->json([
            'recommendations' => $recommendations,
            'count' => $recommendations->count(),
        ]);
    }

    /**
     * Enregistrer une interaction utilisateur.
     */
    public function trackInteraction(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:pieces,id',
            'interaction_type' => 'required|in:view,click,add_to_cart,wishlist,compare',
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        $piece = Piece::findOrFail($request->product_id);

        UserProductInteraction::create([
            'user_id' => $user->id,
            'piece_id' => $piece->id,
            'interaction_type' => $request->interaction_type,
            'interaction_date' => now(),
        ]);

        return response()->json([
            'message' => 'Interaction enregistrée',
            'success' => true,
        ]);
    }

    /**
     * Recommandations basées sur l'historique d'achat.
     */
    public function basedOnPurchases()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $purchasedProducts = OrderItem::whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('status', 'completed');
            })
            ->with('piece')
            ->get()
            ->pluck('piece')
            ->filter()
            ->unique('id');

        $recommendations = collect();

        foreach ($purchasedProducts->take(5) as $piece) {
            $similar = $this->recommendationService
                ->getSimilarProducts($piece, 4);
            $recommendations = $recommendations->merge($similar);
        }

        $recommendations = $recommendations->unique('id')
            ->whereNotIn('id', $purchasedProducts->pluck('id'))
            ->take(12);

        return view('recommendations.purchases', compact(
            'purchasedProducts',
            'recommendations'
        ));
    }

    /**
     * Recommandations pour un véhicule.
     */
    public function forVehicle($vehicleId)
    {
        $vehicle = Vehicle::findOrFail($vehicleId);

        $compatibleProducts = Piece::whereHas('compatibleVehicles', function ($query) use ($vehicle) {
                $query->where('vehicle_id', $vehicle->id);
            })
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->with(['category', 'brand'])
            ->paginate(24);

        $popularForVehicle = Piece::whereHas('compatibleVehicles', function ($query) use ($vehicle) {
                $query->where('vehicles.make', $vehicle->make)
                      ->where('vehicles.model', $vehicle->model);
            })
            ->withCount('orderItems')
            ->orderBy('order_items_count', 'desc')
            ->limit(8)
            ->get();

        return view('recommendations.vehicle', compact(
            'vehicle',
            'compatibleProducts',
            'popularForVehicle'
        ));
    }

    /**
     * Recherche avec recommandations.
     */
    public function search(Request $request)
    {
        $query = $request->input('q');

        $results = Piece::where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhere('nom', 'LIKE', "%{$query}%");
            })
            ->where('is_active', true)
            ->paginate(20);

        if ($results->count() < 5) {
            $recommendations = $this->recommendationService
                ->getPopularProducts(8);
        } else {
            $recommendations = collect();
        }

        return view('recommendations.search', compact(
            'query',
            'results',
            'recommendations'
        ));
    }

    /**
     * Statistiques recommandations (admin).
     */
    public function stats()
    {
        $this->authorize('admin');

        $stats = [
            'total_interactions' => UserProductInteraction::count(),
            'total_views' => UserProductInteraction::where('interaction_type', 'view')->count(),
            'total_clicks' => UserProductInteraction::where('interaction_type', 'click')->count(),
            'most_viewed' => Piece::withCount(['interactions' => function ($query) {
                    $query->where('interaction_type', 'view');
                }])
                ->orderBy('interactions_count', 'desc')
                ->limit(10)
                ->get(),
            'most_clicked' => Piece::withCount(['interactions' => function ($query) {
                    $query->where('interaction_type', 'click');
                }])
                ->orderBy('interactions_count', 'desc')
                ->limit(10)
                ->get(),
        ];

        return view('admin.recommendations.stats', compact('stats'));
    }
}
