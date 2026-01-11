<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Models\UserProductInteraction;
use Illuminate\Support\Collection;

class RecommendationService
{
    public function getRecommendations($userId, $limit = 10)
    {
        // Algorithme basé sur l'historique d'achat et de navigation
        $userInteractions = UserProductInteraction::where('user_id', $userId)
            ->with('product')
            ->get()
            ->groupBy('product_id');

        $productIds = $userInteractions->keys();

        // Trouver des produits similaires
        $recommendations = Product::whereIn('category_id', function($query) use ($productIds) {
                $query->select('category_id')
                    ->from('products')
                    ->whereIn('id', $productIds);
            })
            ->whereNotIn('id', $productIds)
            ->withAvg('reviews as avg_rating', 'rating')
            ->orderByDesc('avg_rating')
            ->limit($limit)
            ->get();

        // Algorithme collaboratif: utilisateurs similaires
        $similarUsers = $this->findSimilarUsers($userId);
        
        $collaborativeRecs = Product::whereIn('id', function($query) use ($similarUsers, $productIds) {
                $query->select('product_id')
                    ->from('user_product_interactions')
                    ->whereIn('user_id', $similarUsers)
                    ->whereNotIn('product_id', $productIds)
                    ->where('type', 'purchase');
            })
            ->limit($limit)
            ->get();

        return $recommendations->merge($collaborativeRecs)->unique('id')->take($limit);
    }

    private function findSimilarUsers($userId)
    {
        // Trouver des utilisateurs ayant acheté des produits similaires
        return User::whereHas('productInteractions', function($query) use ($userId) {
                $query->whereIn('product_id', function($subquery) use ($userId) {
                    $subquery->select('product_id')
                        ->from('user_product_interactions')
                        ->where('user_id', $userId)
                        ->where('type', 'purchase');
                });
            })
            ->where('id', '!=', $userId)
            ->limit(50)
            ->pluck('id');
    }

    public function trackInteraction($userId, $productId, $type)
    {
        $scores = ['view' => 1, 'cart' => 3, 'purchase' => 10];

        UserProductInteraction::create([
            'user_id' => $userId,
            'product_id' => $productId,
            'type' => $type,
            'score' => $scores[$type],
            'occurred_at' => now()
        ]);
    }
}
