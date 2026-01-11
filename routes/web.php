<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PieceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\VehicleSearchController;
use App\Http\Controllers\Admin\ReviewModerationController;

/*
|--------------------------------------------------------------------------
| Routes publiques
|--------------------------------------------------------------------------
*/

// Accueil public = liste des pièces
Route::get('/', [PieceController::class, 'index'])->name('pieces.index');

// Catalogue produits
Route::get('/produits', [CatalogueController::class, 'index'])->name('produits.index');
Route::get('/produits/{piece}', [CatalogueController::class, 'show'])->name('produits.show');

// Panier (session)
Route::prefix('panier')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/ajouter/{piece}', [CartController::class, 'add'])->name('add');
    Route::patch('/{piece}', [CartController::class, 'update'])->name('update');
    Route::delete('/{piece}', [CartController::class, 'remove'])->name('remove');
    Route::delete('/', [CartController::class, 'clear'])->name('clear');
});

// Recherche par véhicule / immatriculation (public)
Route::prefix('vehicules')->name('vehicle.')->group(function () {
    Route::get('/recherche', [VehicleSearchController::class, 'index'])->name('search');
    Route::post('/recherche', [VehicleSearchController::class, 'search'])->name('search.post');
    Route::get('/produits-compatibles/{vehicle}', [VehicleSearchController::class, 'compatibleProducts'])->name('compatible');
    
    // API pour sélecteur véhicule
    Route::get('/api/marques', [VehicleSearchController::class, 'getMakes'])->name('api.makes');
    Route::get('/api/modeles/{make}', [VehicleSearchController::class, 'getModels'])->name('api.models');
    Route::get('/api/annees/{make}/{model}', [VehicleSearchController::class, 'getYears'])->name('api.years');
});

// Avis produits (lecture publique)
Route::prefix('avis')->name('reviews.')->group(function () {
    Route::get('/produit/{product}', [ReviewController::class, 'index'])->name('index');
    Route::get('/{review}', [ReviewController::class, 'show'])->name('show');
});

// Recommandations publiques
Route::prefix('recommandations')->name('recommendations.')->group(function () {
    Route::get('/produit/{product}', [RecommendationController::class, 'forProduct'])->name('product');
    Route::get('/categorie/{category}', [RecommendationController::class, 'forCategory'])->name('category');
    Route::get('/recherche', [RecommendationController::class, 'search'])->name('search');
    
    // API
    Route::get('/api/rapide', [RecommendationController::class, 'quick'])->name('api.quick');
});

/*
|--------------------------------------------------------------------------
| Routes authentifiées
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware('verified')->name('dashboard');

    // CRUD pièces (sauf index qui est public)
    Route::resource('pieces', PieceController::class)->except(['index']);

    // Commande / Checkout
    Route::prefix('commande')->name('checkout.')->group(function () {
        Route::get('/', [CheckoutController::class, 'create'])->name('create');
        Route::post('/', [CheckoutController::class, 'store'])->name('store');
        Route::get('/confirmation/{order}', [CheckoutController::class, 'confirmation'])->name('confirmation');
    });

    // Profil utilisateur (Breeze)
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // Liste de souhaits (Wishlist)
    Route::prefix('wishlist')->name('wishlist.')->group(function () {
        Route::get('/', [WishlistController::class, 'index'])->name('index');
        Route::post('/ajouter/{product}', [WishlistController::class, 'add'])->name('add');
        Route::delete('/{product}', [WishlistController::class, 'remove'])->name('remove');
        Route::delete('/', [WishlistController::class, 'clear'])->name('clear');
    });

    // Comparateur de produits
    Route::prefix('comparaison')->name('comparison.')->group(function () {
        Route::get('/', [ComparisonController::class, 'index'])->name('index');
        Route::post('/ajouter/{product}', [ComparisonController::class, 'add'])->name('add');
        Route::delete('/{product}', [ComparisonController::class, 'remove'])->name('remove');
        Route::delete('/', [ComparisonController::class, 'clear'])->name('clear');
    });

    // Avis produits (écriture)
    Route::prefix('avis')->name('reviews.')->group(function () {
        Route::get('/creer/{product}', [ReviewController::class, 'create'])->name('create');
        Route::post('/creer/{product}', [ReviewController::class, 'store'])->name('store');
        Route::get('/{review}/modifier', [ReviewController::class, 'edit'])->name('edit');
        Route::patch('/{review}', [ReviewController::class, 'update'])->name('update');
        Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('destroy');
        
        // Vote sur les avis
        Route::post('/{review}/voter', [ReviewController::class, 'vote'])->name('vote');
    });

    // Recommandations personnalisées
    Route::prefix('recommandations')->name('recommendations.')->group(function () {
        Route::get('/', [RecommendationController::class, 'index'])->name('index');
        Route::get('/achats', [RecommendationController::class, 'basedOnPurchases'])->name('purchases');
        Route::get('/vehicule/{vehicle}', [RecommendationController::class, 'forVehicle'])->name('vehicle');
        Route::post('/api/panier', [RecommendationController::class, 'forCart'])->name('api.cart');
        Route::post('/api/interaction', [RecommendationController::class, 'trackInteraction'])->name('api.track');
    });

    // Programme de fidélité
    Route::prefix('fidelite')->name('loyalty.')->group(function () {
        Route::get('/', [LoyaltyController::class, 'index'])->name('index');
        Route::get('/recompenses', [LoyaltyController::class, 'rewards'])->name('rewards');
        Route::post('/echanger/{reward}', [LoyaltyController::class, 'redeemReward'])->name('redeem');
        Route::get('/historique', [LoyaltyController::class, 'history'])->name('history');
        Route::get('/statistiques', [LoyaltyController::class, 'stats'])->name('stats');
    });

    // Mes commandes
    Route::prefix('mes-commandes')->name('orders.')->group(function () {
        Route::get('/', function () {
            $orders = auth()->user()->orders()->latest()->paginate(10);
            return view('orders.index', compact('orders'));
        })->name('index');
        
        Route::get('/{order}', function ($orderId) {
            $order = auth()->user()->orders()->findOrFail($orderId);
            return view('orders.show', compact('order'));
        })->name('show');
    });
});

/*
|--------------------------------------------------------------------------
| Routes administrateur
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard admin
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Modération des avis
    Route::prefix('avis')->name('reviews.')->group(function () {
        Route::get('/', [ReviewModerationController::class, 'index'])->name('index');
        Route::get('/{review}', [ReviewModerationController::class, 'show'])->name('show');
        Route::patch('/{review}/approuver', [ReviewModerationController::class, 'approve'])->name('approve');
        Route::patch('/{review}/rejeter', [ReviewModerationController::class, 'reject'])->name('reject');
        Route::delete('/{review}', [ReviewModerationController::class, 'destroy'])->name('destroy');
    });

    // Gestion des utilisateurs
    Route::prefix('utilisateurs')->name('users.')->group(function () {
        Route::get('/', function () {
            $users = \App\Models\User::paginate(20);
            return view('admin.users.index', compact('users'));
        })->name('index');
        
        Route::patch('/{user}/admin', function ($userId) {
            $user = \App\Models\User::findOrFail($userId);
            $user->is_admin = !$user->is_admin;
            $user->save();
            return back()->with('success', 'Statut administrateur modifié');
        })->name('toggle-admin');
    });

    // Gestion des récompenses fidélité
    Route::resource('recompenses', \App\Http\Controllers\Admin\LoyaltyRewardController::class)
        ->names('rewards');

    // Statistiques recommandations
    Route::get('/statistiques/recommandations', [RecommendationController::class, 'stats'])
        ->name('recommendations.stats');

    // Statistiques fidélité
    Route::get('/statistiques/fidelite', [LoyaltyController::class, 'adminStats'])
        ->name('loyalty.stats');

    // Gestion des véhicules
    Route::prefix('vehicules')->name('vehicles.')->group(function () {
        Route::get('/', function () {
            $vehicles = \App\Models\Vehicle::paginate(20);
            return view('admin.vehicles.index', compact('vehicles'));
        })->name('index');
        
        Route::get('/recherches', function () {
            $lookups = \App\Models\LicensePlateLookup::with('vehicle')
                ->orderBy('lookup_count', 'desc')
                ->paginate(20);
            return view('admin.vehicles.lookups', compact('lookups'));
        })->name('lookups');
    });

    // Rapports
    Route::prefix('rapports')->name('reports.')->group(function () {
        Route::get('/ventes', function () {
            return view('admin.reports.sales');
        })->name('sales');
        
        Route::get('/produits', function () {
            return view('admin.reports.products');
        })->name('products');
    });
});

/*
|--------------------------------------------------------------------------
| Routes d'authentification (Breeze)
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
