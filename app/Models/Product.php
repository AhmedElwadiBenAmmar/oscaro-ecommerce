<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'sale_price',
        'cost_price',
        'sku',
        'barcode',
        'quantity',
        'weight',
        'dimensions',
        'category_id',
        'brand_id',
        'is_active',
        'is_featured',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'quantity' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'dimensions' => 'array',
    ];

    // ============================================
    // RELATIONS
    // ============================================

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the brand that owns the product.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get all wishlists for the product.
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Get all comparisons for the product.
     */
    public function comparisons(): HasMany
    {
        return $this->hasMany(Comparison::class);
    }

    /**
     * Get all reviews for the product.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get approved reviews only.
     */
    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('status', 'approved');
    }

    /**
     * Get all interactions for this product.
     */
    public function interactions(): HasMany
    {
        return $this->hasMany(UserProductInteraction::class);
    }

    /**
     * Get the compatible vehicles for this product.
     */
    public function compatibleVehicles(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class, 'product_vehicle_compatibility');
    }

    /**
     * Get all images for the product.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Get the main image for the product.
     */
    public function mainImage(): HasMany
    {
        return $this->images()->where('is_main', true);
    }

    /**
     * Get all order items for this product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ============================================
    // WISHLIST METHODS
    // ============================================

    /**
     * Check if product is in user's wishlist.
     */
    public function isInWishlist(?int $userId = null): bool
    {
        if (!$userId) {
            $userId = auth()->id();
        }

        if (!$userId) {
            return false;
        }

        return $this->wishlists()
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Get wishlist count for this product.
     */
    public function wishlistCount(): int
    {
        return $this->wishlists()->count();
    }

    // ============================================
    // COMPARISON METHODS
    // ============================================

    /**
     * Check if product is in user's comparison list.
     */
    public function isInComparison(?int $userId = null): bool
    {
        if (!$userId) {
            $userId = auth()->id();
        }

        if (!$userId) {
            return false;
        }

        return $this->comparisons()
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Get comparison count for this product.
     */
    public function comparisonCount(): int
    {
        return $this->comparisons()->count();
    }

    // ============================================
    // REVIEW METHODS
    // ============================================

    /**
     * Get average rating for the product.
     */
    public function getAverageRating(): float
    {
        return round($this->approvedReviews()->avg('rating') ?? 0, 2);
    }

    /**
     * Get total reviews count.
     */
    public function getReviewsCount(): int
    {
        return $this->approvedReviews()->count();
    }

    /**
     * Get reviews count by rating.
     */
    public function getReviewsCountByRating(int $rating): int
    {
        return $this->approvedReviews()->where('rating', $rating)->count();
    }

    /**
     * Get rating distribution.
     */
    public function getRatingDistribution(): array
    {
        $total = $this->getReviewsCount();
        $distribution = [];

        for ($i = 5; $i >= 1; $i--) {
            $count = $this->getReviewsCountByRating($i);
            $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
            
            $distribution[$i] = [
                'count' => $count,
                'percentage' => $percentage,
            ];
        }

        return $distribution;
    }

    /**
     * Check if user has reviewed this product.
     */
    public function hasReviewedBy(?int $userId = null): bool
    {
        if (!$userId) {
            $userId = auth()->id();
        }

        if (!$userId) {
            return false;
        }

        return $this->reviews()
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Get user's review for this product.
     */
    public function getReviewBy(?int $userId = null): ?Review
    {
        if (!$userId) {
            $userId = auth()->id();
        }

        if (!$userId) {
            return null;
        }

        return $this->reviews()
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Get verified purchase reviews.
     */
    public function verifiedReviews(): HasMany
    {
        return $this->approvedReviews()->where('verified_purchase', true);
    }

    /**
     * Get verified purchase reviews count.
     */
    public function getVerifiedReviewsCount(): int
    {
        return $this->verifiedReviews()->count();
    }

    // ============================================
    // INTERACTION METHODS
    // ============================================

    /**
     * Get views count.
     */
    public function viewsCount(): int
    {
        return $this->interactions()->views()->count();
    }

    /**
     * Get cart additions count.
     */
    public function cartAdditionsCount(): int
    {
        return $this->interactions()->cart()->count();
    }

    /**
     * Get purchases count.
     */
    public function purchasesCount(): int
    {
        return $this->interactions()->purchases()->count();
    }

    /**
     * Get total interaction score.
     */
    public function totalInteractionScore(): int
    {
        return $this->interactions()->sum('score');
    }

    /**
     * Get popularity rank (based on recent interactions).
     */
    public function getPopularityRank(int $days = 30): int
    {
        $score = $this->interactions()
            ->recent($days)
            ->sum('score');

        return UserProductInteraction::where('occurred_at', '>=', now()->subDays($days))
            ->select('product_id', \DB::raw('SUM(score) as total_score'))
            ->groupBy('product_id')
            ->havingRaw('SUM(score) > ?', [$score])
            ->count() + 1;
    }

    /**
     * Track user interaction with this product.
     */
    public function trackInteraction(?int $userId, string $type): ?UserProductInteraction
    {
        if (!$userId) {
            return null;
        }

        return UserProductInteraction::track($userId, $this->id, $type);
    }

    // ============================================
    // VEHICLE COMPATIBILITY METHODS
    // ============================================

    /**
     * Check if product is compatible with a vehicle.
     */
    public function isCompatibleWith(int $vehicleId): bool
    {
        return $this->compatibleVehicles()
            ->where('vehicle_id', $vehicleId)
            ->exists();
    }

    /**
     * Get compatible vehicles count.
     */
    public function compatibleVehiclesCount(): int
    {
        return $this->compatibleVehicles()->count();
    }

    // ============================================
    // PRICE METHODS
    // ============================================

    /**
     * Get the current price (sale price if available, otherwise regular price).
     */
    public function getCurrentPrice(): float
    {
        return $this->sale_price ?? $this->price;
    }

    /**
     * Check if product is on sale.
     */
    public function isOnSale(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }

    /**
     * Get discount percentage.
     */
    public function getDiscountPercentage(): ?float
    {
        if (!$this->isOnSale()) {
            return null;
        }

        return round((($this->price - $this->sale_price) / $this->price) * 100, 0);
    }

    /**
     * Get profit margin.
     */
    public function getProfitMargin(): ?float
    {
        if (!$this->cost_price) {
            return null;
        }

        $sellingPrice = $this->getCurrentPrice();
        return round((($sellingPrice - $this->cost_price) / $sellingPrice) * 100, 2);
    }

    // ============================================
    // STOCK METHODS
    // ============================================

    /**
     * Check if product is in stock.
     */
    public function isInStock(): bool
    {
        return $this->quantity > 0;
    }

    /**
     * Check if product is low stock.
     */
    public function isLowStock(int $threshold = 5): bool
    {
        return $this->quantity > 0 && $this->quantity <= $threshold;
    }

    /**
     * Check if product is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    /**
     * Get stock status.
     */
    public function getStockStatus(): string
    {
        if ($this->isOutOfStock()) {
            return 'out_of_stock';
        }

        if ($this->isLowStock()) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    /**
     * Decrease stock quantity.
     */
    public function decreaseStock(int $quantity): bool
    {
        if ($this->quantity < $quantity) {
            return false;
        }

        $this->decrement('quantity', $quantity);
        return true;
    }

    /**
     * Increase stock quantity.
     */
    public function increaseStock(int $quantity): void
    {
        $this->increment('quantity', $quantity);
    }

    // ============================================
    // SCOPE METHODS
    // ============================================

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include featured products.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include in-stock products.
     */
    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * Scope a query to only include products on sale.
     */
    public function scopeOnSale($query)
    {
        return $query->whereNotNull('sale_price')
            ->whereColumn('sale_price', '<', 'price');
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope a query to filter by brand.
     */
    public function scopeByBrand($query, int $brandId)
    {
        return $query->where('brand_id', $brandId);
    }

    /**
     * Scope a query to filter by price range.
     */
    public function scopePriceBetween($query, float $min, float $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    /**
     * Scope a query to search products.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('sku', 'like', "%{$search}%")
              ->orWhere('barcode', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to order by popularity.
     */
    public function scopePopular($query, int $days = 30)
    {
        return $query->withCount(['interactions as popularity_score' => function($q) use ($days) {
            $q->where('occurred_at', '>=', now()->subDays($days))
              ->selectRaw('SUM(score)');
        }])->orderByDesc('popularity_score');
    }

    /**
     * Scope to order by best rated.
     */
    public function scopeBestRated($query)
    {
        return $query->withAvg('approvedReviews as avg_rating', 'rating')
            ->orderByDesc('avg_rating');
    }

    /**
     * Scope to order by newest.
     */
    public function scopeNewest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope to order by price.
     */
    public function scopeOrderByPrice($query, string $direction = 'asc')
    {
        return $query->orderBy('price', $direction);
    }

    // ============================================
    // ACCESSOR & MUTATOR
    // ============================================

    /**
     * Get the product's formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2, ',', ' ') . ' €';
    }

    /**
     * Get the product's formatted sale price.
     */
    public function getFormattedSalePriceAttribute(): ?string
    {
        if (!$this->sale_price) {
            return null;
        }

        return number_format($this->sale_price, 2, ',', ' ') . ' €';
    }

    /**
     * Get the product's formatted current price.
     */
    public function getFormattedCurrentPriceAttribute(): string
    {
        return number_format($this->getCurrentPrice(), 2, ',', ' ') . ' €';
    }

    /**
     * Get the product's URL.
     */
    public function getUrlAttribute(): string
    {
        return route('products.show', $this->slug);
    }

    /**
     * Get the product's image URL.
     */
    public function getImageUrlAttribute(): ?string
    {
        $mainImage = $this->mainImage()->first();
        return $mainImage ? asset('storage/' . $mainImage->path) : asset('images/no-image.png');
    }

    // ============================================
    // BOOT METHOD
    // ============================================

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from name
        static::creating(function ($product) {
            if (!$product->slug) {
                $product->slug = \Illuminate\Support\Str::slug($product->name);
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') && !$product->isDirty('slug')) {
                $product->slug = \Illuminate\Support\Str::slug($product->name);
            }
        });
    }
}
