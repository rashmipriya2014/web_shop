# Web Shop API
composer create-project laravel/laravel webshop

php artisan make:command ImportData
php artisan import:data

php artisan make:model Customer
php artisan make:model Product
php artisan make:model Order

php artisan make:model OrderProduct -m 
php artisan make:model ModelName -m

php artisan migrate --path=/database/migrations/2023_08_26_054519_create_order_products_table.php

php artisan migrate