<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PieceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\CartController;

// Accueil public = liste des pièces (admin)
Route::get('/', [PieceController::class, 'index'])->name('pieces.index');

Route::get('/produits', 'App\Http\Controllers\CatalogueController@index')->name('produits.index');
Route::get('/produits/{piece}', 'App\Http\Controllers\CatalogueController@show')->name('produits.show');


// Panier (session)
Route::get('/panier', [CartController::class, 'index'])->name('cart.index');
Route::post('/panier/ajouter/{piece}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/panier/{piece}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/panier/{piece}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/panier', [CartController::class, 'clear'])->name('cart.clear');


// Dashboard protégé
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Routes protégées par auth
Route::middleware('auth')->group(function () {
    // CRUD pièces sauf index (déjà public)
    Route::resource('pieces', PieceController::class)->except(['index']);

    // Profil Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
