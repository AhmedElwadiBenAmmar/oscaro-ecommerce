<?php

namespace App\Http\Controllers;

use App\Models\VehicleMake;
use App\Models\VehicleModel;
use App\Models\VehicleEngine;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    // Formulaire principal (home ou page dédiée)
    public function form()
    {
        $makes = VehicleMake::orderBy('name')->get();

        return view('vehicle.select', compact('makes'));
    }

    // Ajax : modèles par marque
    public function models(Request $request)
    {
        $request->validate([
            'make_id' => 'required|exists:vehicle_makes,id',
        ]);

        $models = VehicleModel::where('vehicle_make_id', $request->make_id)
            ->orderBy('name')
            ->get();

        return response()->json($models);
    }

    // Ajax : moteurs par modèle
    public function engines(Request $request)
    {
        $request->validate([
            'model_id' => 'required|exists:vehicle_models,id',
        ]);

        $engines = VehicleEngine::where('vehicle_model_id', $request->model_id)
            ->orderBy('name')
            ->get();

        return response()->json($engines);
    }

    // Validation du véhicule choisi
    public function select(Request $request)
    {
        $request->validate([
            'engine_id' => 'required|exists:vehicle_engines,id',
        ]);

        session(['selected_engine_id' => $request->engine_id]);

        return redirect()->route('catalogue.index')
            ->with('success', 'Véhicule sélectionné.');
    }
}
