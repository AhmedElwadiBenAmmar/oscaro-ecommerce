<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyPoint extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'loyalty_points';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'total_points',
        'available_points',
        'used_points',
        'expired_points',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_points' => 'integer',
        'available_points' => 'integer',
        'used_points' => 'integer',
        'expired_points' => 'integer',
    ];

    /**
     * Get the user that owns the loyalty points.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all transactions for this loyalty account.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class, 'user_id', 'user_id');
    }

    /**
     * Get recent transactions.
     */
    public function recentTransactions()
    {
        return $this->transactions()
            ->orderBy('created_at', 'desc')
            ->limit(10);
    }

    /**
     * Get pending (non-expired) credit transactions.
     */
    public function pendingCredits()
    {
        return $this->transactions()
            ->where('operation', 'credit')
            ->where('expired', false)
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now());
    }

    /**
     * Add points to the account.
     *
     * @param int $points
     * @return void
     */
    public function addPoints(int $points): void
    {
        $this->total_points += $points;
        $this->available_points += $points;
        $this->save();
    }

    /**
     * Deduct points from the account.
     *
     * @param int $points
     * @return bool
     */
    public function deductPoints(int $points): bool
    {
        if ($this->available_points < $points) {
            return false;
        }

        $this->available_points -= $points;
        $this->used_points += $points;
        $this->save();

        return true;
    }

    /**
     * Expire points.
     *
     * @param int $points
     * @return void
     */
    public function expirePoints(int $points): void
    {
        $pointsToExpire = min($points, $this->available_points);
        
        $this->available_points -= $pointsToExpire;
        $this->expired_points += $pointsToExpire;
        $this->save();
    }

    /**
     * Check if user has enough points.
     *
     * @param int $points
     * @return bool
     */
    public function hasEnoughPoints(int $points): bool
    {
        return $this->available_points >= $points;
    }

    /**
     * Get the percentage of points used.
     *
     * @return float
     */
    public function getUsagePercentageAttribute(): float
    {
        if ($this->total_points === 0) {
            return 0.0;
        }

        return round(($this->used_points / $this->total_points) * 100, 2);
    }

    /**
     * Get the percentage of points expired.
     *
     * @return float
     */
    public function getExpiredPercentageAttribute(): float
    {
        if ($this->total_points === 0) {
            return 0.0;
        }

        return round(($this->expired_points / $this->total_points) * 100, 2);
    }

    /**
     * Get points that will expire soon (within 30 days).
     *
     * @return int
     */
    public function getPointsExpiringSoonAttribute(): int
    {
        return $this->transactions()
            ->where('operation', 'credit')
            ->where('expired', false)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays(30)])
            ->sum('points');
    }

    /**
     * Get loyalty tier based on total points.
     *
     * @return string
     */
    public function getTierAttribute(): string
    {
        if ($this->total_points >= 10000) {
            return 'Platine';
        } elseif ($this->total_points >= 5000) {
            return 'Or';
        } elseif ($this->total_points >= 2000) {
            return 'Argent';
        } elseif ($this->total_points >= 500) {
            return 'Bronze';
        }

        return 'Standard';
    }

    /**
     * Get tier color for UI.
     *
     * @return string
     */
    public function getTierColorAttribute(): string
    {
        return match($this->tier) {
            'Platine' => '#E5E4E2',
            'Or' => '#FFD700',
            'Argent' => '#C0C0C0',
            'Bronze' => '#CD7F32',
            default => '#6B7280',
        };
    }

    /**
     * Get tier benefits description.
     *
     * @return array
     */
    public function getTierBenefitsAttribute(): array
    {
        return match($this->tier) {
            'Platine' => [
                'Réduction de 15% sur tous les produits',
                'Livraison gratuite illimitée',
                'Accès prioritaire aux ventes privées',
                'Support client premium 24/7',
                'Points bonus x3 sur tous les achats',
            ],
            'Or' => [
                'Réduction de 10% sur tous les produits',
                'Livraison gratuite sur commandes > 50€',
                'Accès anticipé aux ventes privées',
                'Points bonus x2 sur tous les achats',
            ],
            'Argent' => [
                'Réduction de 5% sur tous les produits',
                'Livraison gratuite sur commandes > 100€',
                'Points bonus x1.5 sur tous les achats',
            ],
            'Bronze' => [
                'Réduction de 3% sur tous les produits',
                'Offres exclusives membres',
            ],
            default => [
                'Accumulez des points sur vos achats',
            ],
        };
    }

    /**
     * Get points needed for next tier.
     *
     * @return int|null
     */
    public function getPointsToNextTierAttribute(): ?int
    {
        $nextThresholds = [
            'Standard' => 500,
            'Bronze' => 2000,
            'Argent' => 5000,
            'Or' => 10000,
            'Platine' => null,
        ];

        $nextThreshold = $nextThresholds[$this->tier] ?? null;

        if ($nextThreshold === null) {
            return null; // Déjà au niveau maximum
        }

        return $nextThreshold - $this->total_points;
    }

    /**
     * Get summary of loyalty account.
     *
     * @return array
     */
    public function getSummary(): array
    {
        return [
            'user' => $this->user->name,
            'tier' => $this->tier,
            'tier_color' => $this->tier_color,
            'total_points' => $this->total_points,
            'available_points' => $this->available_points,
            'used_points' => $this->used_points,
            'expired_points' => $this->expired_points,
            'points_expiring_soon' => $this->points_expiring_soon,
            'points_to_next_tier' => $this->points_to_next_tier,
            'usage_percentage' => $this->usage_percentage,
            'tier_benefits' => $this->tier_benefits,
        ];
    }

    /**
     * Scope to get accounts with expiring points.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithExpiringPoints($query, int $days = 30)
    {
        return $query->whereHas('transactions', function($q) use ($days) {
            $q->where('operation', 'credit')
              ->where('expired', false)
              ->whereNotNull('expires_at')
              ->whereBetween('expires_at', [now(), now()->addDays($days)]);
        });
    }

    /**
     * Scope to get accounts by tier.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $tier
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByTier($query, string $tier)
    {
        $thresholds = [
            'Platine' => ['min' => 10000, 'max' => null],
            'Or' => ['min' => 5000, 'max' => 9999],
            'Argent' => ['min' => 2000, 'max' => 4999],
            'Bronze' => ['min' => 500, 'max' => 1999],
            'Standard' => ['min' => 0, 'max' => 499],
        ];

        if (!isset($thresholds[$tier])) {
            return $query;
        }

        $range = $thresholds[$tier];
        $query->where('total_points', '>=', $range['min']);

        if ($range['max'] !== null) {
            $query->where('total_points', '<=', $range['max']);
        }

        return $query;
    }

    /**
     * Scope to get active accounts (with available points).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('available_points', '>', 0);
    }

    /**
     * Scope to get top loyalty members.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTopMembers($query, int $limit = 10)
    {
        return $query->orderBy('total_points', 'desc')->limit($limit);
    }

    /**
     * Boot method.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($loyaltyPoint) {
            if (!isset($loyaltyPoint->total_points)) {
                $loyaltyPoint->total_points = 0;
            }
            if (!isset($loyaltyPoint->available_points)) {
                $loyaltyPoint->available_points = 0;
            }
            if (!isset($loyaltyPoint->used_points)) {
                $loyaltyPoint->used_points = 0;
            }
            if (!isset($loyaltyPoint->expired_points)) {
                $loyaltyPoint->expired_points = 0;
            }
        });
    }
}
