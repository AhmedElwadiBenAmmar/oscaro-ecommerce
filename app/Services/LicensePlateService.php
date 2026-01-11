<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\Product;
use App\Models\LicensePlateLookup;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LicensePlateService
{
    /**
     * API pour la recherche d'immatriculation (exemple avec l'API SIV française)
     * Vous pouvez utiliser une API comme:
     * - API SIV (Service Immatriculation Véhicules)
     * - CarQuery API
     * - NHTSA API (USA)
     * - DVLA API (UK)
     */
    const API_URL = 'https://api.example.com/vehicle-lookup'; // À remplacer
    const API_KEY = null; // À configurer dans .env

    /**
     * Durée de cache en secondes (7 jours)
     */
    const CACHE_DURATION = 604800;

    /**
     * Rechercher un véhicule par plaque d'immatriculation
     *
     * @param string $licensePlate
     * @return Vehicle|null
     */
    public function searchByLicensePlate(string $licensePlate): ?Vehicle
    {
        // Nettoyer la plaque d'immatriculation
        $cleanPlate = $this->cleanLicensePlate($licensePlate);

        // Vérifier si on a déjà cette info en cache
        $cacheKey = "vehicle_lookup_{$cleanPlate}";
        
        if (Cache::has($cacheKey)) {
            $vehicleData = Cache::get($cacheKey);
            return $this->findOrCreateVehicle($vehicleData);
        }

        // Vérifier dans notre base de données
        $lookup = LicensePlateLookup::where('license_plate', $cleanPlate)->first();
        
        if ($lookup && $lookup->vehicle) {
            return $lookup->vehicle;
        }

        // Appeler l'API externe
        try {
            $vehicleData = $this->callExternalAPI($cleanPlate);
            
            if ($vehicleData) {
                // Enregistrer le résultat
                $vehicle = $this->findOrCreateVehicle($vehicleData);
                
                // Enregistrer la recherche
                $this->saveLookup($cleanPlate, $vehicle);
                
                // Mettre en cache
                Cache::put($cacheKey, $vehicleData, self::CACHE_DURATION);
                
                return $vehicle;
            }
        } catch (\Exception $e) {
            Log::error("Erreur lors de la recherche d'immatriculation: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Nettoyer la plaque d'immatriculation
     *
     * @param string $licensePlate
     * @return string
     */
    protected function cleanLicensePlate(string $licensePlate): string
    {
        // Retirer les espaces, tirets et mettre en majuscules
        return strtoupper(preg_replace('/[^A-Z0-9]/', '', $licensePlate));
    }

    /**
     * Appeler l'API externe pour obtenir les infos du véhicule
     *
     * @param string $licensePlate
     * @return array|null
     */
    protected function callExternalAPI(string $licensePlate): ?array
    {
        // EXEMPLE D'IMPLÉMENTATION
        // À adapter selon l'API que vous utilisez
        
        try {
            $apiKey = config('services.vehicle_api.key') ?? self::API_KEY;
            
            if (!$apiKey) {
                // Mode simulation pour développement
                return $this->getMockVehicleData($licensePlate);
            }

            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Accept' => 'application/json',
                ])
                ->get(self::API_URL, [
                    'registration' => $licensePlate,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'make' => $data['make'] ?? null,
                    'model' => $data['model'] ?? null,
                    'year' => $data['year'] ?? null,
                    'fuel_type' => $data['fuel_type'] ?? null,
                    'engine_size' => $data['engine_size'] ?? null,
                    'engine_code' => $data['engine_code'] ?? null,
                    'transmission' => $data['transmission'] ?? null,
                    'body_type' => $data['body_type'] ?? null,
                    'color' => $data['color'] ?? null,
                    'vin' => $data['vin'] ?? null,
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Erreur API véhicule: " . $e->getMessage());
            
            // En cas d'erreur, retourner des données de test
            return $this->getMockVehicleData($licensePlate);
        }
    }

    /**
     * Données de test pour le développement
     *
     * @param string $licensePlate
     * @return array
     */
    protected function getMockVehicleData(string $licensePlate): array
    {
        // Générer des données de test basées sur la plaque
        $makes = ['Renault', 'Peugeot', 'Citroën', 'Volkswagen', 'BMW', 'Mercedes'];
        $models = ['Clio', '208', 'C3', 'Golf', 'Serie 3', 'Classe A'];
        $fuelTypes = ['Essence', 'Diesel', 'Hybride', 'Électrique'];
        $transmissions = ['Manuelle', 'Automatique'];
        $bodyTypes = ['Berline', 'SUV', 'Citadine', 'Break'];

        $index = crc32($licensePlate) % count($makes);

        return [
            'make' => $makes[$index],
            'model' => $models[$index],
            'year' => rand(2015, 2024),
            'fuel_type' => $fuelTypes[array_rand($fuelTypes)],
            'engine_size' => rand(10, 30) / 10 . 'L',
            'engine_code' => 'ENG' . rand(100, 999),
            'transmission' => $transmissions[array_rand($transmissions)],
            'body_type' => $bodyTypes[array_rand($bodyTypes)],
            'color' => null,
            'vin' => 'VIN' . strtoupper(substr(md5($licensePlate), 0, 14)),
        ];
    }

    /**
     * Trouver ou créer un véhicule dans la base de données
     *
     * @param array $vehicleData
     * @return Vehicle
     */
    protected function findOrCreateVehicle(array $vehicleData): Vehicle
    {
        // Chercher d'abord par VIN si disponible
        if (!empty($vehicleData['vin'])) {
            $vehicle = Vehicle::where('vin', $vehicleData['vin'])->first();
            if ($vehicle) {
                return $vehicle;
            }
        }

        // Sinon chercher par combinaison marque/modèle/année/motorisation
        $vehicle = Vehicle::where('make', $vehicleData['make'])
            ->where('model', $vehicleData['model'])
            ->where('year', $vehicleData['year'])
            ->where('engine_code', $vehicleData['engine_code'] ?? null)
            ->first();

        if ($vehicle) {
            return $vehicle;
        }

        // Créer un nouveau véhicule
        return Vehicle::create([
            'make' => $vehicleData['make'],
            'model' => $vehicleData['model'],
            'year' => $vehicleData['year'],
            'fuel_type' => $vehicleData['fuel_type'] ?? null,
            'engine_size' => $vehicleData['engine_size'] ?? null,
            'engine_code' => $vehicleData['engine_code'] ?? null,
            'transmission' => $vehicleData['transmission'] ?? null,
            'body_type' => $vehicleData['body_type'] ?? null,
            'vin' => $vehicleData['vin'] ?? null,
        ]);
    }

    /**
     * Enregistrer la recherche d'immatriculation
     *
     * @param string $licensePlate
     * @param Vehicle $vehicle
     * @return LicensePlateLookup
     */
    protected function saveLookup(string $licensePlate, Vehicle $vehicle): LicensePlateLookup
    {
        return LicensePlateLookup::updateOrCreate(
            ['license_plate' => $licensePlate],
            [
                'vehicle_id' => $vehicle->id,
                'lookup_count' => \DB::raw('lookup_count + 1'),
                'last_lookup_at' => now(),
            ]
        );
    }

    /**
     * Rechercher des produits compatibles avec un véhicule
     *
     * @param Vehicle $vehicle
     * @param string|null $category
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCompatibleProducts(Vehicle $vehicle, ?string $category = null)
    {
        $query = Product::whereHas('compatibleVehicles', function($q) use ($vehicle) {
            $q->where('vehicle_id', $vehicle->id);
        });

        if ($category) {
            $query->whereHas('category', function($q) use ($category) {
                $q->where('slug', $category);
            });
        }

        return $query->with(['category', 'brand'])
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();
    }

    /**
     * Obtenir les statistiques de recherche
     *
     * @return array
     */
    public function getLookupStats(): array
    {
        return [
            'total_lookups' => LicensePlateLookup::sum('lookup_count'),
            'unique_plates' => LicensePlateLookup::count(),
            'vehicles_found' => LicensePlateLookup::whereNotNull('vehicle_id')->count(),
            'recent_lookups' => LicensePlateLookup::orderBy('last_lookup_at', 'desc')
                ->limit(10)
                ->get(),
            'most_searched' => LicensePlateLookup::orderBy('lookup_count', 'desc')
                ->limit(10)
                ->get(),
        ];
    }

    /**
     * Valider le format d'une plaque d'immatriculation
     *
     * @param string $licensePlate
     * @param string $country
     * @return bool
     */
    public function validateLicensePlate(string $licensePlate, string $country = 'FR'): bool
    {
        $patterns = [
            'FR' => [
                '/^[A-Z]{2}-\d{3}-[A-Z]{2}$/',           // Nouveau format: AB-123-CD
                '/^\d{1,4}\s?[A-Z]{2,3}\s?\d{2,3}$/',   // Ancien format: 123 AB 01
            ],
            'BE' => '/^[A-Z0-9]{1}-[A-Z]{3}-\d{3}$/',    // Belgique: 1-ABC-123
            'DE' => '/^[A-Z]{1,3}-[A-Z]{1,2}\d{1,4}$/',  // Allemagne: B-AB-1234
            'ES' => '/^\d{4}[A-Z]{3}$/',                  // Espagne: 1234ABC
            'IT' => '/^[A-Z]{2}\d{3}[A-Z]{2}$/',         // Italie: AB123CD
            'UK' => '/^[A-Z]{2}\d{2}[A-Z]{3}$/',         // UK: AB12CDE
        ];

        $licensePlate = strtoupper(str_replace([' ', '-'], '', $licensePlate));

        if (!isset($patterns[$country])) {
            return false;
        }

        $countryPatterns = is_array($patterns[$country]) ? $patterns[$country] : [$patterns[$country]];

        foreach ($countryPatterns as $pattern) {
            if (preg_match($pattern, $licensePlate)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtenir des suggestions de véhicules similaires
     *
     * @param Vehicle $vehicle
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSimilarVehicles(Vehicle $vehicle, int $limit = 5)
    {
        return Vehicle::where('id', '!=', $vehicle->id)
            ->where(function($query) use ($vehicle) {
                $query->where('make', $vehicle->make)
                    ->orWhere('model', $vehicle->model)
                    ->orWhere('year', $vehicle->year);
            })
            ->limit($limit)
            ->get();
    }

    /**
     * Nettoyer les anciennes recherches
     *
     * @param int $daysOld
     * @return int Nombre de lignes supprimées
     */
    public function cleanOldLookups(int $daysOld = 90): int
    {
        return LicensePlateLookup::where('last_lookup_at', '<', now()->subDays($daysOld))
            ->where('lookup_count', '<', 2)
            ->delete();
    }

    /**
     * Recherche par marque et modèle
     *
     * @param string $make
     * @param string $model
     * @param int|null $year
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchByMakeModel(string $make, string $model, ?int $year = null)
    {
        $query = Vehicle::where('make', 'LIKE', "%{$make}%")
            ->where('model', 'LIKE', "%{$model}%");

        if ($year) {
            $query->where('year', $year);
        }

        return $query->orderBy('year', 'desc')->get();
    }

    /**
     * Obtenir toutes les marques disponibles
     *
     * @return array
     */
    public function getAvailableMakes(): array
    {
        return Vehicle::distinct()
            ->orderBy('make')
            ->pluck('make')
            ->toArray();
    }

    /**
     * Obtenir les modèles pour une marque
     *
     * @param string $make
     * @return array
     */
    public function getModelsForMake(string $make): array
    {
        return Vehicle::where('make', $make)
            ->distinct()
            ->orderBy('model')
            ->pluck('model')
            ->toArray();
    }

    /**
     * Obtenir les années pour une marque et un modèle
     *
     * @param string $make
     * @param string $model
     * @return array
     */
    public function getYearsForMakeModel(string $make, string $model): array
    {
        return Vehicle::where('make', $make)
            ->where('model', $model)
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
    }
}
