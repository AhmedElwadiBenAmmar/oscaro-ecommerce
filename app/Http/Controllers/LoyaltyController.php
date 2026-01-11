<?php

namespace App\Http\Controllers;

use App\Services\LoyaltyService;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    protected $loyaltyService;

    public function __construct(LoyaltyService $loyaltyService)
    {
        $this->loyaltyService = $loyaltyService;
    }

    public function index()
    {
        $user = auth()->user();
        $availablePoints = $this->loyaltyService->getAvailablePoints($user);
        $transactions = $this->loyaltyService->getTransactionHistory($user);
        $rewards = $this->loyaltyService->getAvailableRewards($user);
        $stats = $this->loyaltyService->getUserStats($user);

        return view('loyalty.index', compact('availablePoints', 'transactions', 'rewards', 'stats'));
    }

    public function redeemReward(Request $request, $rewardId)
    {
        $user = auth()->user();
        $reward = \App\Models\LoyaltyReward::findOrFail($rewardId);

        try {
            $transaction = $this->loyaltyService->redeemReward($user, $reward);
            return redirect()->back()->with('success', 'Récompense échangée avec succès !');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
