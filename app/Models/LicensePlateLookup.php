<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicensePlateLookup extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'license_plate_lookups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'license_plate',
        'vehicle_id',
        'lookup_count',
        'last_lookup_at',
        'country_code',
        'api_response',
        'is_successful',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_lookup_at' => 'datetime',
        'api_response' => 'array',
        'is_successful' => 'boolean',
        'lookup_count' => 'integer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'api_response',
    ];

    /**
     * Get the vehicle associated with this lookup.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Scope to get most searched license plates
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMostSearched($query, int $limit = 10)
    {
        return $query->orderBy('lookup_count', 'desc')->limit($limit);
    }

    /**
     * Scope to get recent lookups
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('last_lookup_at', 'desc')->limit($limit);
    }

    /**
     * Scope to get successful lookups
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSuccessful($query)
    {
        return $query->where('is_successful', true);
    }

    /**
     * Scope to get failed lookups
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('is_successful', false);
    }

    /**
     * Scope to filter by country code
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $countryCode
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCountry($query, string $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    /**
     * Increment the lookup count
     *
     * @return void
     */
    public function incrementLookupCount(): void
    {
        $this->increment('lookup_count');
        $this->update(['last_lookup_at' => now()]);
    }

    /**
     * Get formatted license plate
     *
     * @return string
     */
    public function getFormattedLicensePlateAttribute(): string
    {
        $plate = $this->license_plate;

        // Format selon le pays
        switch ($this->country_code) {
            case 'FR':
                // Format français: AB-123-CD
                if (strlen($plate) === 7) {
                    return substr($plate, 0, 2) . '-' . substr($plate, 2, 3) . '-' . substr($plate, 5, 2);
                }
                break;
            
            case 'BE':
                // Format belge: 1-ABC-123
                if (strlen($plate) === 7) {
                    return substr($plate, 0, 1) . '-' . substr($plate, 1, 3) . '-' . substr($plate, 4, 3);
                }
                break;

            case 'DE':
                // Format allemand: B-AB-1234
                if (strlen($plate) >= 5) {
                    return substr($plate, 0, 1) . '-' . substr($plate, 1);
                }
                break;
        }

        return $plate;
    }

    /**
     * Check if lookup is recent (within last 7 days)
     *
     * @return bool
     */
    public function isRecent(): bool
    {
        return $this->last_lookup_at && $this->last_lookup_at->greaterThan(now()->subDays(7));
    }

    /**
     * Check if lookup is popular (more than 5 searches)
     *
     * @return bool
     */
    public function isPopular(): bool
    {
        return $this->lookup_count > 5;
    }

    /**
     * Get the vehicle details if available
     *
     * @return array|null
     */
    public function getVehicleDetailsAttribute(): ?array
    {
        if (!$this->vehicle) {
            return null;
        }

        return [
            'make' => $this->vehicle->make,
            'model' => $this->vehicle->model,
            'year' => $this->vehicle->year,
            'fuel_type' => $this->vehicle->fuel_type,
            'engine_size' => $this->vehicle->engine_size,
        ];
    }

    /**
     * Boot method to set default values
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lookup) {
            if (!$lookup->lookup_count) {
                $lookup->lookup_count = 1;
            }

            if (!$lookup->last_lookup_at) {
                $lookup->last_lookup_at = now();
            }

            if (!$lookup->country_code) {
                $lookup->country_code = 'FR'; // Par défaut France
            }
        });
    }
}

