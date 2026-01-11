<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewVote extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'review_votes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'review_id',
        'user_id',
        'is_helpful',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_helpful' => 'boolean',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Get the review that owns the vote.
     */
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    /**
     * Get the user who voted.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include helpful votes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHelpful($query)
    {
        return $query->where('is_helpful', true);
    }

    /**
     * Scope a query to only include not helpful votes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotHelpful($query)
    {
        return $query->where('is_helpful', false);
    }

    /**
     * Scope a query for a specific review.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $reviewId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForReview($query, $reviewId)
    {
        return $query->where('review_id', $reviewId);
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
     * Check if user has already voted on a review.
     *
     * @param int $reviewId
     * @param int $userId
     * @return bool
     */
    public static function hasVoted($reviewId, $userId): bool
    {
        return static::where('review_id', $reviewId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Get user's vote on a review.
     *
     * @param int $reviewId
     * @param int $userId
     * @return self|null
     */
    public static function getUserVote($reviewId, $userId): ?self
    {
        return static::where('review_id', $reviewId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Get helpful votes count for a review.
     *
     * @param int $reviewId
     * @return int
     */
    public static function helpfulCount($reviewId): int
    {
        return static::where('review_id', $reviewId)
            ->where('is_helpful', true)
            ->count();
    }

    /**
     * Get not helpful votes count for a review.
     *
     * @param int $reviewId
     * @return int
     */
    public static function notHelpfulCount($reviewId): int
    {
        return static::where('review_id', $reviewId)
            ->where('is_helpful', false)
            ->count();
    }

    /**
     * Get total votes count for a review.
     *
     * @param int $reviewId
     * @return int
     */
    public static function totalCount($reviewId): int
    {
        return static::where('review_id', $reviewId)->count();
    }

    /**
     * Calculate helpfulness percentage.
     *
     * @param int $reviewId
     * @return float
     */
    public static function helpfulnessPercentage($reviewId): float
    {
        $total = static::totalCount($reviewId);
        
        if ($total === 0) {
            return 0.0;
        }

        $helpful = static::helpfulCount($reviewId);
        
        return round(($helpful / $total) * 100, 2);
    }
}
