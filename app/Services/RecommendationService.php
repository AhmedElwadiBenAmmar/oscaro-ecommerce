<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Piece;
use App\Models\User;
use App\Models\UserProductInteraction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    /**
     * Recommandations personnalisées pour un utilisateur.
     */
    public function getPersonalizedRecommendations(User $user, int $limit = 12): Collection
    {
        // 1. Produits basés sur les interactions (vues, clics, wishlist, panier)
        $interactedProductIds = UserProductInteraction::where('user_id', $user->id)
            ->orderBy('interaction_date', 'desc')
            ->limit(50)
            ->pluck('piece_id')   // on utilise piece_id car tes produits sont des "pieces"
            ->toArray();

        // 2. Produits de mêmes catégories
        $categoryIds = DB::table('pieces')
            ->whereIn('id', $interactedProductIds)
            ->pluck('category_id')
            ->unique()
            ->toArray();

        $query = Piece::query()
            ->where('is_active', true)
            ->where('stock', '>', 0);

        if (!empty($categoryIds)) {
            $query->whereIn('category_id', $categoryIds);
        }

        if (!empty($interactedProductIds)) {
            $query->whereNotIn('id', $interactedProductIds);
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Produits populaires (fallback).
     */
    public function getPopularProducts(int $limit = 8): Collection
    {
        return Piece::query()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->withCount('orderItems')
            ->orderBy('order_items_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Produits récemment vus.
     */
    public function getRecentlyViewedProducts(User $user, int $limit = 6): Collection
    {
        $ids = UserProductInteraction::where('user_id', $user->id)
            ->where('interaction_type', 'view')
            ->orderBy('interaction_date', 'desc')
            ->limit(50)
            ->pluck('piece_id')
            ->unique()
            ->take($limit)
            ->toArray();

        return Piece::whereIn('id', $ids)->get();
    }

    /**
     * Produits tendances.
     */
    public function getTrendingProducts(int $limit = 6): Collection
    {
        $since = now()->subDays(7);

        $ids = UserProductInteraction::where('interaction_date', '>=', $since)
            ->select('piece_id', DB::raw('count(*) as interactions_count'))
            ->groupBy('piece_id')
            ->orderByDesc('interactions_count')
            ->limit(50)
            ->pluck('piece_id')
            ->toArray();

        return Piece::whereIn('id', $ids)
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->limit($limit)
            ->get();
    }

    /**
     * Produits similaires à un produit donné.
     */
    public function getSimilarProducts(Piece $product, int $limit = 8): Collection
    {
        return Piece::where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('price')
            ->limit($limit)
            ->get();
    }

    /**
     * Produits fréquemment achetés ensemble.
     */
    public function getFrequentlyBoughtTogether(Piece $product, int $limit = 4): Collection
    {
        $ids = DB::table('order_items as oi1')
            ->join('order_items as oi2', function ($join) use ($product) {
                $join->on('oi1.order_id', '=', 'oi2.order_id')
                     ->where('oi1.piece_id', '=', $product->id)
                     ->where('oi2.piece_id', '!=', $product->id);
            })
            ->select('oi2.piece_id', DB::raw('count(*) as freq'))
            ->groupBy('oi2.piece_id')
            ->orderByDesc('freq')
            ->limit(50)
            ->pluck('piece_id')
            ->toArray();

        return Piece::whereIn('id', $ids)
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->limit($limit)
            ->get();
    }

    /**
     * Produits complémentaires.
     */
    public function getComplementaryProducts(Piece $product, int $limit = 6): Collection
    {
        return Piece::where('id', '!=', $product->id)
            ->where('category_id', '!=', $product->category_id)
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
