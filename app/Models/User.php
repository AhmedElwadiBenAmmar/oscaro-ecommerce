<?php

namespace App\Models;


// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;



class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    // ============================================
    // RELATIONS
    // ============================================

    /**
     * Get the wishlists for the user.
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Get the comparisons for the user.
     */
   // app/Models/User.php
   public function comparisons()
   {
       return $this->hasMany(\App\Models\Comparison::class);
   }
   




    /**
     * Get all reviews written by the user.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get all review votes by the user.
     */
    public function reviewVotes(): HasMany
    {
        return $this->hasMany(ReviewVote::class);
    }

    /**
     * Get all product interactions for the user.
     */
    public function productInteractions(): HasMany
    {
        return $this->hasMany(UserProductInteraction::class);
    }

    /**
     * Get the loyalty account for the user.
     */
    public function loyaltyAccount(): HasMany
    {
        return $this->hasMany(LoyaltyPoint::class);
    }

    /**
     * Get all loyalty transactions for the user.
     */
    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    /**
     * Get all orders for the user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // ============================================
    // ADMIN METHODS
    // ============================================

    /**
     * Check if user is an administrator.
     */
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    // ============================================
    // WISHLIST METHODS
    // ============================================

    /**
     * Check if a product is in user's wishlist.
     */
    public function hasInWishlist(int $productId): bool
    {
        return $this->wishlists()
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Add a product to wishlist.
     */
    public function addToWishlist(int $productId): bool
    {
        if ($this->hasInWishlist($productId)) {
            return false;
        }

        $this->wishlists()->create([
            'product_id' => $productId,
            'added_at' => now(),
        ]);

        return true;
    }

    /**
     * Remove a product from wishlist.
     */
    public function removeFromWishlist(int $productId): bool
    {
        return $this->wishlists()
            ->where('product_id', $productId)
            ->delete() > 0;
    }

    // ============================================
    // COMPARISON METHODS
    // ============================================

    /**
     * Get the count of products in comparison list.
     */
    public function comparisonCount(): int
    {
        return $this->comparisons()->count();
    }

    /**
     * Check if a product is in user's comparison list.
     */
    public function hasInComparison(int $productId): bool
    {
        return $this->comparisons()
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Add a product to comparison list.
     */
    public function addToComparison(int $productId): bool
    {
        if ($this->hasInComparison($productId)) {
            return false;
        }

        $this->comparisons()->create([
            'product_id' => $productId,
            'added_at' => now(),
        ]);

        return true;
    }

    /**
     * Remove a product from comparison list.
     */
    public function removeFromComparison(int $productId): bool
    {
        return $this->comparisons()
            ->where('product_id', $productId)
            ->delete() > 0;
    }

    /**
     * Clear all comparisons.
     */
    public function clearComparisons(): int
    {
        return $this->comparisons()->delete();
    }

    // ============================================
    // REVIEW VOTE METHODS
    // ============================================

    /**
     * Check if user has voted on a review.
     */
    public function hasVotedOn(int $reviewId): bool
    {
        return $this->reviewVotes()
            ->where('review_id', $reviewId)
            ->exists();
    }

    /**
     * Get user's vote on a specific review.
     */
    public function getVoteFor(int $reviewId): ?ReviewVote
    {
        return $this->reviewVotes()
            ->where('review_id', $reviewId)
            ->first();
    }

    /**
     * Vote on a review.
     */
    public function voteOnReview(int $reviewId, bool $isHelpful): ReviewVote
    {
        return $this->reviewVotes()->updateOrCreate(
            ['review_id' => $reviewId],
            ['is_helpful' => $isHelpful]
        );
    }

    /**
     * Remove vote from a review.
     */
    public function removeVoteFrom(int $reviewId): bool
    {
        return $this->reviewVotes()
            ->where('review_id', $reviewId)
            ->delete() > 0;
    }

    // ============================================
    // PRODUCT INTERACTION METHODS
    // ============================================

    /**
     * Track a product interaction.
     */
    public function trackInteraction(int $productId, string $type): UserProductInteraction
    {
        return UserProductInteraction::track($this->id, $productId, $type);
    }

    /**
     * Get user's interaction history.
     */
    public function getInteractionHistory(int $limit = 50)
    {
        return $this->productInteractions()
            ->with('product')
            ->latest('occurred_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get user's most interacted products.
     */
    public function getMostInteractedProducts(int $limit = 10)
    {
        return UserProductInteraction::getMostInteractedProducts($this->id, $limit);
    }

    /**
     * Get user's interaction statistics.
     */
    public function getInteractionStatistics(): array
    {
        return UserProductInteraction::getStatistics($this->id);
    }

    /**
     * Check if user has interacted with a product.
     */
    public function hasInteractedWith(int $productId, ?string $type = null): bool
    {
        $query = $this->productInteractions()->where('product_id', $productId);
        
        if ($type) {
            $query->where('type', $type);
        }
        
        return $query->exists();
    }

    /**
     * Get last interaction with a product.
     */
    public function getLastInteractionWith(int $productId): ?UserProductInteraction
    {
        return $this->productInteractions()
            ->where('product_id', $productId)
            ->latest('occurred_at')
            ->first();
    }

    // ============================================
    // LOYALTY METHODS
    // ============================================

    /**
     * Get user's loyalty points balance.
     */
    public function getLoyaltyPoints(): int
    {
        return $this->loyaltyAccount()->value('points') ?? 0;
    }

    /**
     * Get user's lifetime loyalty points.
     */
    public function getLifetimeLoyaltyPoints(): int
    {
        return $this->loyaltyAccount()->value('lifetime_points') ?? 0;
    }

    /**
     * Get user's loyalty tier.
     */
    public function getLoyaltyTier(): string
    {
        return $this->loyaltyAccount()->value('tier') ?? 'bronze';
    }

    /**
     * Check if user has enough loyalty points.
     */
    public function hasEnoughPoints(int $points): bool
    {
        return $this->getLoyaltyPoints() >= $points;
    }

    // ============================================
    // ORDER METHODS
    // ============================================

    /**
     * Check if user has purchased a specific product.
     */
    public function hasPurchased(int $productId): bool
    {
        return $this->orders()
            ->whereHas('items', fn($q) => $q->where('product_id', $productId))
            ->where('status', 'delivered')
            ->exists();
    }

    /**
     * Get user's total spending.
     */
    public function getTotalSpending(): float
    {
        return $this->orders()
            ->where('status', 'delivered')
            ->sum('total_amount');
    }

    /**
     * Get user's order count.
     */
    public function getOrderCount(): int
    {
        return $this->orders()
            ->where('status', 'delivered')
            ->count();
    }
}
