# Translation Management Service

## Overview
A high-performance API-driven translation management service built with Laravel, designed to handle 100k+ translations with response times under 200ms.

## Setup Instructions

### Prerequisites
- PHP 8.1+
- Composer
- MySQL 8.0+
- Laravel 10.x

### Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd translation-service
```

2. Install dependencies:
```bash
composer install
```

3. Configure environment:
```bash
cp .env.example .env
php artisan key:generate
```

4. Update `.env` with database credentials:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=translation_service
DB_USERNAME=root
DB_PASSWORD=
```

5. Run migrations:
```bash
php artisan migrate
```

6. Seed database with test data (100k+ records):
```bash
php artisan translations:seed
```

## API Endpoints

### Base URL
```
http://localhost:8000
```

### Endpoints

#### 1. Create Translation
```http
POST /api/translations
Content-Type: application/json

{
    "key": "welcome.message",
    "locale": "en",
    "value": "Welcome to our application",
    "tags": ["web", "mobile"]
}
```

#### 2. Update Translation
```http
PUT /api/translations/{id}
Content-Type: application/json

{
    "value": "Updated translation text",
    "tags": ["web"]
}
```

#### 3. Get Single Translation
```http
GET /api/translations/{id}
```

#### 4. Search Translations
```http
GET /api/translations/search?key=welcome&locale=en&tags=web,mobile&content=application
```

#### 5. List All Translations
```http
GET /api/translations?page=1&per_page=10
```

#### 6. Delete Translation
```http
DELETE /api/translations/{id}
```

#### 7. Export Translations (JSON for Frontend)
```http
GET /api/translations/export?locale=en&tags=web
```

Response format:
```json
{
    "locale": "en",
    "translations": {
        "welcome.message": "Welcome to our application",
        "button.submit": "Submit"
    }
}
```

## Design Choices

### Database Schema
- **translations table**: Stores translation records with indexed columns for fast lookups
- **tags table**: Normalized tag storage
- **translation_tag pivot table**: Many-to-many relationship between translations and tags
- **Indexes**: Added on `key`, `locale`, and composite indexes for common queries

### Performance Optimizations
1. **Database Indexing**: Strategic indexes on frequently queried columns
2. **Query Optimization**: Select only required columns, eager loading relationships
3. **Caching**: Redis caching for export endpoint with tag-based invalidation
4. **Batch Operations**: Efficient bulk inserts for seeding

### Architecture
- **Repository Pattern**: Separates data access logic from business logic
- **Service Layer**: Handles business logic and coordinates between repositories
- **Resource Classes**: Transforms data for API responses
- **Request Validation**: Dedicated form request classes for input validation

### SOLID Principles Applied
- **Single Responsibility**: Each class has one clear purpose
- **Open/Closed**: Extensible through interfaces without modification
- **Liskov Substitution**: Repository interface allows swapping implementations
- **Interface Segregation**: Focused interfaces for specific operations
- **Dependency Inversion**: Depends on abstractions (interfaces) not concrete classes

## Performance Testing

Run performance tests:
```bash
php artisan test --filter=PerformanceTest
```

Expected results:
- Standard endpoints: < 200ms
- Export endpoint: < 500ms (even with 100k+ records)

## Testing

Run all tests:
```bash
php artisan test
```

Run with coverage:
```bash
php artisan test --coverage
```

## Code Quality

This project follows:
- PSR-12 coding standards
- SOLID design principles
- Clean code practices
- Comprehensive test coverage

Check code style:
```bash
./vendor/bin/pint --test
```

Fix code style:
```bash
./vendor/bin/pint
```

## Project Structure

```
app/
├── Console/
│   └── Commands/
│       └── SeedTranslations.php
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── TranslationController.php
│   ├── Requests/
│   │   ├── StoreTranslationRequest.php
│   │   └── UpdateTranslationRequest.php
│   └── Resources/
│       ├── TranslationResource.php
│       └── TagResource.php
├── Models/
│   ├── Translation.php
│   └── Tag.php
├── Repositories/
│   ├── Contracts/
│   │   └── TranslationRepositoryInterface.php
│   └── TranslationRepository.php
├── Services/
│   └── TranslationService.php
└── Providers/
    └── AppServiceProvider.php

database/
├── factories/
│   ├── TranslationFactory.php
│   └── TagFactory.php
└── migrations/
    └── xxxx_create_translations_tables.php

routes/
└── api.php

tests/
├── Feature/
│   ├── TranslationTest.php
│   └── PerformanceTest.php
└── Unit/
    └── TranslationServiceTest.php
```
