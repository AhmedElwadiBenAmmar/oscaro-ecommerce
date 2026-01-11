<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyReward;
use Illuminate\Http\Request;

class LoyaltyRewardController extends Controller
{
    public function index()
    {
        $rewards = LoyaltyReward::paginate(20);
        return view('admin.rewards.index', compact('rewards'));
    }

    public function create()
    {
        return view('admin.rewards.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'points_required' => 'required|integer|min:1',
            'type' => 'required|string',
            'value' => 'nullable|numeric',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        LoyaltyReward::create($data);

        return redirect()->route('admin.rewards.index')
            ->with('success', 'Récompense créée.');
    }

    public function edit(LoyaltyReward $recompense)
    {
        return view('admin.rewards.edit', ['reward' => $recompense]);
    }

    public function update(Request $request, LoyaltyReward $recompense)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'points_required' => 'required|integer|min:1',
            'type' => 'required|string',
            'value' => 'nullable|numeric',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $recompense->update($data);

        return redirect()->route('admin.rewards.index')
            ->with('success', 'Récompense mise à jour.');
    }

    public function destroy(LoyaltyReward $recompense)
    {
        $recompense->delete();

        return redirect()->route('admin.rewards.index')
            ->with('success', 'Récompense supprimée.');
    }
}
