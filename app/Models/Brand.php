<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Brand extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'brands';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the pieces for the brand.
     */
    public function pieces(): HasMany
    {
        return $this->hasMany(Piece::class);
    }

    /**
     * Get active pieces for the brand.
     */
    public function activePieces(): HasMany
    {
        return $this->pieces()->where('is_active', true);
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Scope a query to only include active brands.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order brands by name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByName($query, string $direction = 'asc')
    {
        return $query->orderBy('name', $direction);
    }

    /**
     * Scope to get brands with pieces count.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithPiecesCount($query)
    {
        return $query->withCount('pieces');
    }

    /**
     * Get the logo URL.
     *
     * @return string|null
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) {
            return null;
        }

        // Si le logo est une URL complète
        if (Str::startsWith($this->logo, ['http://', 'https://'])) {
            return $this->logo;
        }

        // Sinon, c'est un chemin relatif dans storage
        return asset('storage/' . $this->logo);
    }

    /**
     * Get the number of active pieces.
     *
     * @return int
     */
    public function getActivePiecesCountAttribute(): int
    {
        return $this->activePieces()->count();
    }

    /**
     * Check if brand has pieces.
     *
     * @return bool
     */
    public function hasPieces(): bool
    {
        return $this->pieces()->exists();
    }

    /**
     * Get the most popular pieces for this brand.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function popularPieces(int $limit = 10)
    {
        return $this->pieces()
            ->where('is_active', true)
            ->withCount('orderItems')
            ->orderBy('order_items_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get the newest pieces for this brand.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function newestPieces(int $limit = 10)
    {
        return $this->pieces()
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get brand statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'total_pieces' => $this->pieces()->count(),
            'active_pieces' => $this->activePieces()->count(),
            'total_sales' => $this->pieces()
                ->join('order_items', 'pieces.id', '=', 'order_items.piece_id')
                ->sum('order_items.quantity'),
            'total_revenue' => $this->pieces()
                ->join('order_items', 'pieces.id', '=', 'order_items.piece_id')
                ->sum('order_items.total'),
            'average_rating' => $this->pieces()
                ->join('reviews', 'pieces.id', '=', 'reviews.piece_id')
                ->where('reviews.status', 'approved')
                ->avg('reviews.rating'),
        ];
    }

    /**
     * Boot method to automatically generate slug.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($brand) {
            if (!$brand->slug) {
                $brand->slug = Str::slug($brand->name);
            }
        });

        static::updating(function ($brand) {
            if ($brand->isDirty('name') && !$brand->isDirty('slug')) {
                $brand->slug = Str::slug($brand->name);
            }
        });
    }
}
