<?php

namespace App\Services;

use App\Models\Piece;
use App\Models\User;
use App\Models\UserProductInteraction;
use App\Models\Vehicle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    /**
     * Recommandations personnalisées pour un utilisateur (filtrées éventuellement par véhicule).
     */
    public function getPersonalizedRecommendations(User $user, int $limit = 12, ?Vehicle $vehicle = null): Collection
    {
        $interactedProductIds = UserProductInteraction::where('user_id', $user->id)
            ->orderBy('interaction_date', 'desc')
            ->limit(50)
            ->pluck('piece_id')
            ->toArray();

        $categoryIds = Piece::whereIn('id', $interactedProductIds)
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

        if ($vehicle) {
            $query->whereHas('compatibleVehicles', function ($q) use ($vehicle) {
                $q->where('vehicle_id', $vehicle->id);
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Produits populaires (fallback).
     */
    public function getPopularProducts(int $limit = 8, ?Vehicle $vehicle = null): Collection
    {
        $query = Piece::query()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->withCount('orderItems')
            ->orderBy('order_items_count', 'desc');

        if ($vehicle) {
            $query->whereHas('compatibleVehicles', function ($q) use ($vehicle) {
                $q->where('vehicle_id', $vehicle->id);
            });
        }

        return $query->limit($limit)->get();
    }

    /**
     * Produits récemment vus.
     */
    public function getRecentlyViewedProducts(User $user, int $limit = 6, ?Vehicle $vehicle = null): Collection
    {
        $ids = UserProductInteraction::where('user_id', $user->id)
            ->where('interaction_type', 'view')
            ->orderBy('interaction_date', 'desc')
            ->limit(50)
            ->pluck('piece_id')
            ->unique()
            ->take($limit)
            ->toArray();

        $query = Piece::whereIn('id', $ids)
            ->where('is_active', true)
            ->where('stock', '>', 0);

        if ($vehicle) {
            $query->whereHas('compatibleVehicles', function ($q) use ($vehicle) {
                $q->where('vehicle_id', $vehicle->id);
            });
        }

        return $query->get();
    }

    /**
     * Produits tendances.
     */
    public function getTrendingProducts(int $limit = 6, ?Vehicle $vehicle = null): Collection
    {
        $since = now()->subDays(7);

        $ids = UserProductInteraction::where('interaction_date', '>=', $since)
            ->select('piece_id', DB::raw('count(*) as interactions_count'))
            ->groupBy('piece_id')
            ->orderByDesc('interactions_count')
            ->limit(50)
            ->pluck('piece_id')
            ->toArray();

        $query = Piece::whereIn('id', $ids)
            ->where('is_active', true)
            ->where('stock', '>', 0);

        if ($vehicle) {
            $query->whereHas('compatibleVehicles', function ($q) use ($vehicle) {
                $q->where('vehicle_id', $vehicle->id);
            });
        }

        return $query->limit($limit)->get();
    }

    /**
     * Produits similaires à un produit donné.
     */
    public function getSimilarProducts(Piece $product, int $limit = 8, ?Vehicle $vehicle = null): Collection
    {
        $query = Piece::where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('price');

        if ($vehicle) {
            $query->whereHas('compatibleVehicles', function ($q) use ($vehicle) {
                $q->where('vehicle_id', $vehicle->id);
            });
        }

        return $query->limit($limit)->get();
    }

    /**
     * Produits fréquemment achetés ensemble.
     */
    public function getFrequentlyBoughtTogether(Piece $product, int $limit = 4, ?Vehicle $vehicle = null): Collection
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

        $query = Piece::whereIn('id', $ids)
            ->where('is_active', true)
            ->where('stock', '>', 0);

        if ($vehicle) {
            $query->whereHas('compatibleVehicles', function ($q) use ($vehicle) {
                $q->where('vehicle_id', $vehicle->id);
            });
        }

        return $query->limit($limit)->get();
    }

    /**
     * Produits complémentaires (autres catégories).
     */
    public function getComplementaryProducts(Piece $product, int $limit = 6, ?Vehicle $vehicle = null): Collection
    {
        $query = Piece::where('id', '!=', $product->id)
            ->where('category_id', '!=', $product->category_id)
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('created_at', 'desc');

        if ($vehicle) {
            $query->whereHas('compatibleVehicles', function ($q) use ($vehicle) {
                $q->where('vehicle_id', $vehicle->id);
            });
        }

        return $query->limit($limit)->get();
    }

    /**
     * Recommandations par “job” (kits vidange, freinage, etc.).
     * Nécessite une colonne job_type sur pieces.
     */
    public function getJobBasedRecommendations(Collection $pieces, ?Vehicle $vehicle = null, int $limit = 10): Collection
    {
        $jobTypes = $pieces->pluck('job_type')->filter()->unique()->toArray();

        if (empty($jobTypes)) {
            return collect();
        }

        $query = Piece::whereIn('job_type', $jobTypes)
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->whereNotIn('id', $pieces->pluck('id'));

        if ($vehicle) {
            $query->whereHas('compatibleVehicles', function ($q) use ($vehicle) {
                $q->where('vehicle_id', $vehicle->id);
            });
        }

        return $query->limit($limit)->get();
    }
}
