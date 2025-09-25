
# Al Ibdal Car Rental Backend

This is the Laravel (PHP) backend for the Al Ibdal Car Rental Management Platform.

## Main Entities
- ServiceRequest
- Car
- Branch
- Partner
- Rental
- Invoice
- Contract
- Subcontractor
- User (with roles)

## Setup
1. Install dependencies:
	```
	composer install
	```
2. Copy `.env.example` to `.env` and configure your database settings.
3. Generate app key:
	```
	php artisan key:generate
	```
4. Create models and migrations for all entities:
	```
	php artisan make:model ServiceRequest -m
	php artisan make:model Car -m
	php artisan make:model Branch -m
	php artisan make:model Partner -m
	php artisan make:model Rental -m
	php artisan make:model Invoice -m
	php artisan make:model Contract -m
	php artisan make:model Subcontractor -m
	```
5. Edit migration files in `database/migrations` to define fields and relationships as per PRD.
6. Run migrations:
	```
	php artisan migrate
	```
7. Start the development server:
	```
	php artisan serve
	```

## API Structure
- RESTful endpoints for each entity
- Authentication and role-based access
- Notification integration (Email, SMS, WhatsApp)
- Audit logging

Refer to `Al-Ibdal-Car-Rental-PRD.md` for requirements and data structure details.
