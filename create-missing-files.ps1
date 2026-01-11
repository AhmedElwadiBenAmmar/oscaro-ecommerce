# Version alternative simple
Write-Host "Creation des fichiers..." -ForegroundColor Green

# Migrations
php artisan make:migration create_loyalty_points_table
php artisan make:migration create_loyalty_transactions_table
php artisan make:migration create_loyalty_rewards_table
php artisan make:migration create_vehicles_table
php artisan make:migration create_product_vehicle_compatibility_table
php artisan make:migration create_license_plate_lookups_table
php artisan make:migration add_is_admin_to_users_table --table=users

# Modèles
php artisan make:model LoyaltyPoint
php artisan make:model LoyaltyTransaction
php artisan make:model LoyaltyReward
php artisan make:model Vehicle
php artisan make:model LicensePlateLookup

# Contrôleurs
php artisan make:controller LoyaltyController
php artisan make:controller VehicleSearchController
php artisan make:controller Admin/ReviewModerationController

# Middleware
php artisan make:middleware EnsureUserIsAdmin
php artisan make:middleware TrackProductView

# Commands
php artisan make:command CleanOldComparisons
php artisan make:command CleanOldInteractions
php artisan make:command ExpireLoyaltyPoints

# Dossiers vues
New-Item -ItemType Directory -Force -Path "resources/views/wishlist"
New-Item -ItemType Directory -Force -Path "resources/views/comparison"
New-Item -ItemType Directory -Force -Path "resources/views/reviews/partials"
New-Item -ItemType Directory -Force -Path "resources/views/admin/reviews"
New-Item -ItemType Directory -Force -Path "resources/views/loyalty"
New-Item -ItemType Directory -Force -Path "resources/views/vehicle"

# Fichiers vues
New-Item -ItemType File -Force -Path "resources/views/wishlist/index.blade.php"
New-Item -ItemType File -Force -Path "resources/views/comparison/index.blade.php"
New-Item -ItemType File -Force -Path "resources/views/reviews/index.blade.php"
New-Item -ItemType File -Force -Path "resources/views/reviews/create.blade.php"
New-Item -ItemType File -Force -Path "resources/views/reviews/show.blade.php"
New-Item -ItemType File -Force -Path "resources/views/reviews/partials/vote-buttons.blade.php"
New-Item -ItemType File -Force -Path "resources/views/admin/reviews/moderation.blade.php"
New-Item -ItemType File -Force -Path "resources/views/loyalty/index.blade.php"
New-Item -ItemType File -Force -Path "resources/views/loyalty/rewards.blade.php"
New-Item -ItemType File -Force -Path "resources/views/vehicle/search.blade.php"
New-Item -ItemType File -Force -Path "resources/views/products/compatible.blade.php"

# Services
New-Item -ItemType File -Force -Path "app/Services/LoyaltyService.php"
New-Item -ItemType File -Force -Path "app/Services/LicensePlateService.php"

Write-Host "Termine!" -ForegroundColor Green
