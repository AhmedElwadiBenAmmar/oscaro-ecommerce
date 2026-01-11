<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyTransaction;
use App\Models\LoyaltyReward;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoyaltyService
{
    /**
     * Taux de conversion : 1€ dépensé = X points
     */
    const POINTS_PER_EURO = 10;

    /**
     * Durée de validité des points en mois
     */
    const POINTS_VALIDITY_MONTHS = 12;

    /**
     * Ajouter des points après une commande
     *
     * @param User $user
     * @param Order $order
     * @return LoyaltyTransaction
     */
    public function addPointsFromOrder(User $user, Order $order): LoyaltyTransaction
    {
        $points = $this->calculatePointsFromAmount($order->total);
        
        return $this->addPoints(
            $user,
            $points,
            'order',
            $order->id,
            "Points gagnés pour la commande #{$order->id}"
        );
    }

    /**
     * Ajouter des points à un utilisateur
     *
     * @param User $user
     * @param int $points
     * @param string $type
     * @param int|null $relatedId
     * @param string|null $description
     * @return LoyaltyTransaction
     */
    public function addPoints(
        User $user,
        int $points,
        string $type = 'manual',
        ?int $relatedId = null,
        ?string $description = null
    ): LoyaltyTransaction {
        DB::beginTransaction();
        
        try {
            // Créer ou mettre à jour le solde de points
            $loyaltyPoint = LoyaltyPoint::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'total_points' => 0,
                    'available_points' => 0,
                    'used_points' => 0
                ]
            );

            $loyaltyPoint->total_points += $points;
            $loyaltyPoint->available_points += $points;
            $loyaltyPoint->save();

            // Créer la transaction
            $transaction = LoyaltyTransaction::create([
                'user_id' => $user->id,
                'points' => $points,
                'type' => $type,
                'operation' => 'credit',
                'related_id' => $relatedId,
                'description' => $description ?? "Ajout de {$points} points",
                'balance_after' => $loyaltyPoint->available_points,
                'expires_at' => Carbon::now()->addMonths(self::POINTS_VALIDITY_MONTHS)
            ]);

            DB::commit();

            return $transaction;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Utiliser des points pour une récompense
     *
     * @param User $user
     * @param LoyaltyReward $reward
     * @return LoyaltyTransaction
     * @throws \Exception
     */
    public function redeemReward(User $user, LoyaltyReward $reward): LoyaltyTransaction
    {
        $loyaltyPoint = $user->loyaltyPoints;

        if (!$loyaltyPoint || $loyaltyPoint->available_points < $reward->points_required) {
            throw new \Exception("Points insuffisants pour échanger cette récompense.");
        }

        if (!$reward->is_active) {
            throw new \Exception("Cette récompense n'est plus disponible.");
        }

        if ($reward->stock !== null && $reward->stock <= 0) {
            throw new \Exception("Cette récompense est en rupture de stock.");
        }

        DB::beginTransaction();

        try {
            // Déduire les points
            $loyaltyPoint->available_points -= $reward->points_required;
            $loyaltyPoint->used_points += $reward->points_required;
            $loyaltyPoint->save();

            // Décrémenter le stock si applicable
            if ($reward->stock !== null) {
                $reward->decrement('stock');
            }

            // Créer la transaction
            $transaction = LoyaltyTransaction::create([
                'user_id' => $user->id,
                'points' => $reward->points_required,
                'type' => 'reward',
                'operation' => 'debit',
                'related_id' => $reward->id,
                'description' => "Échange : {$reward->name}",
                'balance_after' => $loyaltyPoint->available_points,
                'reward_data' => json_encode([
                    'reward_id' => $reward->id,
                    'reward_name' => $reward->name,
                    'reward_type' => $reward->type,
                    'reward_value' => $reward->value
                ])
            ]);

            DB::commit();

            return $transaction;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculer les points à partir d'un montant
     *
     * @param float $amount
     * @return int
     */
    public function calculatePointsFromAmount(float $amount): int
    {
        return (int) floor($amount * self::POINTS_PER_EURO);
    }

    /**
     * Obtenir le solde de points d'un utilisateur
     *
     * @param User $user
     * @return int
     */
    public function getAvailablePoints(User $user): int
    {
        $loyaltyPoint = $user->loyaltyPoints;
        return $loyaltyPoint ? $loyaltyPoint->available_points : 0;
    }

    /**
     * Obtenir l'historique des transactions d'un utilisateur
     *
     * @param User $user
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTransactionHistory(User $user, int $limit = 20)
    {
        return LoyaltyTransaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Expirer les points périmés
     *
     * @return int Nombre de points expirés
     */
    public function expirePoints(): int
    {
        $expiredTransactions = LoyaltyTransaction::where('operation', 'credit')
            ->where('expires_at', '<', Carbon::now())
            ->where('expired', false)
            ->get();

        $totalExpired = 0;

        foreach ($expiredTransactions as $transaction) {
            DB::beginTransaction();

            try {
                // Marquer la transaction comme expirée
                $transaction->expired = true;
                $transaction->save();

                // Déduire les points du solde
                $loyaltyPoint = LoyaltyPoint::where('user_id', $transaction->user_id)->first();
                
                if ($loyaltyPoint && $loyaltyPoint->available_points >= $transaction->points) {
                    $pointsToExpire = min($transaction->points, $loyaltyPoint->available_points);
                    
                    $loyaltyPoint->available_points -= $pointsToExpire;
                    $loyaltyPoint->save();

                    // Créer une transaction d'expiration
                    LoyaltyTransaction::create([
                        'user_id' => $transaction->user_id,
                        'points' => $pointsToExpire,
                        'type' => 'expiration',
                        'operation' => 'debit',
                        'related_id' => $transaction->id,
                        'description' => "Expiration de {$pointsToExpire} points",
                        'balance_after' => $loyaltyPoint->available_points
                    ]);

                    $totalExpired += $pointsToExpire;
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error("Erreur lors de l'expiration des points : " . $e->getMessage());
            }
        }

        return $totalExpired;
    }

    /**
     * Obtenir les récompenses disponibles
     *
     * @param User|null $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableRewards(?User $user = null)
    {
        $query = LoyaltyReward::where('is_active', true)
            ->where(function($q) {
                $q->whereNull('stock')
                  ->orWhere('stock', '>', 0);
            })
            ->orderBy('points_required', 'asc');

        if ($user) {
            $availablePoints = $this->getAvailablePoints($user);
            // Ajouter une indication si l'utilisateur peut échanger
            return $query->get()->map(function($reward) use ($availablePoints) {
                $reward->can_redeem = $availablePoints >= $reward->points_required;
                return $reward;
            });
        }

        return $query->get();
    }

    /**
     * Annuler une transaction (admin uniquement)
     *
     * @param LoyaltyTransaction $transaction
     * @param string $reason
     * @return bool
     */
    public function cancelTransaction(LoyaltyTransaction $transaction, string $reason = ''): bool
    {
        if ($transaction->cancelled) {
            throw new \Exception("Cette transaction a déjà été annulée.");
        }

        DB::beginTransaction();

        try {
            $loyaltyPoint = LoyaltyPoint::where('user_id', $transaction->user_id)->first();

            if (!$loyaltyPoint) {
                throw new \Exception("Utilisateur sans compte de points.");
            }

            // Inverser l'opération
            if ($transaction->operation === 'credit') {
                // Annuler un crédit = débit
                $loyaltyPoint->available_points -= $transaction->points;
                $loyaltyPoint->total_points -= $transaction->points;
            } else {
                // Annuler un débit = crédit
                $loyaltyPoint->available_points += $transaction->points;
                $loyaltyPoint->used_points -= $transaction->points;
            }

            $loyaltyPoint->save();

            // Marquer la transaction comme annulée
            $transaction->cancelled = true;
            $transaction->cancelled_at = Carbon::now();
            $transaction->cancellation_reason = $reason;
            $transaction->save();

            // Créer une transaction d'annulation
            LoyaltyTransaction::create([
                'user_id' => $transaction->user_id,
                'points' => $transaction->points,
                'type' => 'cancellation',
                'operation' => $transaction->operation === 'credit' ? 'debit' : 'credit',
                'related_id' => $transaction->id,
                'description' => "Annulation : {$transaction->description}. Raison : {$reason}",
                'balance_after' => $loyaltyPoint->available_points
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtenir les statistiques de fidélité d'un utilisateur
     *
     * @param User $user
     * @return array
     */
    public function getUserStats(User $user): array
    {
        $loyaltyPoint = $user->loyaltyPoints;

        if (!$loyaltyPoint) {
            return [
                'total_points' => 0,
                'available_points' => 0,
                'used_points' => 0,
                'expired_points' => 0,
                'rewards_redeemed' => 0,
                'member_since' => null
            ];
        }

        $expiredPoints = LoyaltyTransaction::where('user_id', $user->id)
            ->where('type', 'expiration')
            ->sum('points');

        $rewardsRedeemed = LoyaltyTransaction::where('user_id', $user->id)
            ->where('type', 'reward')
            ->where('operation', 'debit')
            ->count();

        $firstTransaction = LoyaltyTransaction::where('user_id', $user->id)
            ->orderBy('created_at', 'asc')
            ->first();

        return [
            'total_points' => $loyaltyPoint->total_points,
            'available_points' => $loyaltyPoint->available_points,
            'used_points' => $loyaltyPoint->used_points,
            'expired_points' => $expiredPoints,
            'rewards_redeemed' => $rewardsRedeemed,
            'member_since' => $firstTransaction ? $firstTransaction->created_at : null
        ];
    }
}
