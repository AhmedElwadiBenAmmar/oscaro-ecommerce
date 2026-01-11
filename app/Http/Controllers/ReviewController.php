<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// app/Http/Controllers/ReviewController.php
class ReviewController extends Controller
{
    public function store(Request $request, Product $product) {
        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'title' => 'required|string|max:100',
            'comment' => 'required|string|min:10|max:1000'
        ]);

        // Vérifier si l'utilisateur a acheté le produit
        $verifiedPurchase = auth()->user()->orders()
            ->whereHas('items', fn($q) => $q->where('product_id', $product->id))
            ->where('status', 'delivered')
            ->exists();

        Review::create([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
            'rating' => $validated['rating'],
            'title' => $validated['title'],
            'comment' => $validated['comment'],
            'verified_purchase' => $verifiedPurchase,
            'status' => 'pending'
        ]);

        return back()->with('success', 'Votre avis a été soumis et sera modéré');
    }

    public function vote(Review $review, Request $request) {
        $request->validate(['is_helpful' => 'required|boolean']);

        auth()->user()->reviewVotes()->updateOrCreate(
            ['review_id' => $review->id],
            ['is_helpful' => $request->is_helpful]
        );

        return back();
    }
}


