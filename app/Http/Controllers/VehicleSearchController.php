<?php

namespace App\Http\Controllers;

use App\Services\LicensePlateService;
use Illuminate\Http\Request;

class VehicleSearchController extends Controller
{
    protected $licensePlateService;

    public function __construct(LicensePlateService $licensePlateService)
    {
        $this->licensePlateService = $licensePlateService;
    }

    public function search(Request $request)
    {
        $request->validate([
            'license_plate' => 'required|string|min:3',
        ]);

        $licensePlate = $request->input('license_plate');

        // Valider le format
        if (!$this->licensePlateService->validateLicensePlate($licensePlate)) {
            return back()->withErrors(['license_plate' => 'Format de plaque invalide']);
        }

        // Rechercher le véhicule
        $vehicle = $this->licensePlateService->searchByLicensePlate($licensePlate);

        if (!$vehicle) {
            return back()->withErrors(['license_plate' => 'Véhicule non trouvé']);
        }

        // Récupérer les produits compatibles
        $products = $this->licensePlateService->getCompatibleProducts($vehicle);

        return view('vehicle.results', compact('vehicle', 'products'));
    }

    public function getMakes()
    {
        $makes = $this->licensePlateService->getAvailableMakes();
        return response()->json($makes);
    }

    public function getModels($make)
    {
        $models = $this->licensePlateService->getModelsForMake($make);
        return response()->json($models);
    }

    public function getYears($make, $model)
    {
        $years = $this->licensePlateService->getYearsForMakeModel($make, $model);
        return response()->json($years);
    }
}
