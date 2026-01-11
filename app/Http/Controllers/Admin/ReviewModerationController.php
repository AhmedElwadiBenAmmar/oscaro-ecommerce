<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewModerationController extends Controller
{
    public function __construct()
    {
        // Middleware pour vérifier que l'utilisateur est admin
        $this->middleware(['auth', 'admin']);
    }

    public function index() 
    {
        $reviews = Review::with(['user', 'product'])
            ->pending()
            ->latest()
            ->paginate(20);
        
        return view('admin.reviews.moderation', compact('reviews'));
    }

    public function approve(Review $review) 
    {
        $review->update([
            'status' => 'approved',
            'moderated_by' => auth()->id(),
            'moderated_at' => now()
        ]);

        return back()->with('success', 'Avis approuvé');
    }

    public function reject(Review $review, Request $request) 
    {
        $validated = $request->validate([
            'reason' => 'required|string'
        ]);

        $review->update([
            'status' => 'rejected',
            'moderated_by' => auth()->id(),
            'moderated_at' => now(),
            'moderation_reason' => $validated['reason']
        ]);

        return back()->with('success', 'Avis rejeté');
    }
}
