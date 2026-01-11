<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProductInteraction extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_product_interactions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'type',
        'score',
        'occurred_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'occurred_at' => 'datetime',
        'score' => 'integer',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Interaction types and their scores.
     *
     * @var array
     */
    const TYPES = [
        'view' => 1,
        'cart' => 3,
        'wishlist' => 2,
        'purchase' => 10,
    ];

    /**
     * Get the user who made the interaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product that was interacted with.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope a query to only include interactions of a specific type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include view interactions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeViews($query)
    {
        return $query->where('type', 'view');
    }

    /**
     * Scope a query to only include cart interactions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCart($query)
    {
        return $query->where('type', 'cart');
    }

    /**
     * Scope a query to only include wishlist interactions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWishlist($query)
    {
        return $query->where('type', 'wishlist');
    }

    /**
     * Scope a query to only include purchase interactions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePurchases($query)
    {
        return $query->where('type', 'purchase');
    }

    /**
     * Scope a query for a specific user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query for a specific product.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $productId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to only include recent interactions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('occurred_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to order by most recent.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('occurred_at', 'desc');
    }

    /**
     * Get total interaction score for a user.
     *
     * @param int $userId
     * @return int
     */
    public static function getTotalScoreForUser($userId): int
    {
        return static::where('user_id', $userId)->sum('score');
    }

    /**
     * Get total interaction score for a product.
     *
     * @param int $productId
     * @return int
     */
    public static function getTotalScoreForProduct($productId): int
    {
        return static::where('product_id', $productId)->sum('score');
    }

    /**
     * Track a new interaction.
     *
     * @param int $userId
     * @param int $productId
     * @param string $type
     * @return self
     */
    public static function track($userId, $productId, $type): self
    {
        $score = self::TYPES[$type] ?? 1;

        return static::create([
            'user_id' => $userId,
            'product_id' => $productId,
            'type' => $type,
            'score' => $score,
            'occurred_at' => now(),
        ]);
    }

    /**
     * Get most interacted products for a user.
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public static function getMostInteractedProducts($userId, $limit = 10)
    {
        return static::where('user_id', $userId)
            ->select('product_id', \DB::raw('SUM(score) as total_score'))
            ->groupBy('product_id')
            ->orderByDesc('total_score')
            ->limit($limit)
            ->with('product')
            ->get();
    }

    /**
     * Get popular products based on interactions.
     *
     * @param int $limit
     * @param int $days
     * @return \Illuminate\Support\Collection
     */
    public static function getPopularProducts($limit = 10, $days = 30)
    {
        return static::where('occurred_at', '>=', now()->subDays($days))
            ->select('product_id', \DB::raw('SUM(score) as total_score'))
            ->groupBy('product_id')
            ->orderByDesc('total_score')
            ->limit($limit)
            ->with('product')
            ->get();
    }

    /**
     * Get user's interaction history.
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getHistory($userId, $limit = 50)
    {
        return static::where('user_id', $userId)
            ->with('product')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Clean old interactions.
     *
     * @param int $days
     * @return int
     */
    public static function cleanOld($days = 90): int
    {
        return static::where('occurred_at', '<', now()->subDays($days))
            ->where('type', 'view') // Ne supprimer que les vues
            ->delete();
    }

    /**
     * Get interaction statistics for a user.
     *
     * @param int $userId
     * @return array
     */
    public static function getStatistics($userId): array
    {
        $interactions = static::where('user_id', $userId)->get();

        return [
            'total' => $interactions->count(),
            'views' => $interactions->where('type', 'view')->count(),
            'cart' => $interactions->where('type', 'cart')->count(),
            'wishlist' => $interactions->where('type', 'wishlist')->count(),
            'purchases' => $interactions->where('type', 'purchase')->count(),
            'total_score' => $interactions->sum('score'),
            'unique_products' => $interactions->unique('product_id')->count(),
            'last_interaction' => $interactions->max('occurred_at'),
        ];
    }
}
