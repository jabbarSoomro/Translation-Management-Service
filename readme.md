# Translation Management Service

## Overview
A high-performance API-driven translation management service built with Laravel, designed to handle 100k+ translations with response times under 200ms. Secured with token-based authentication using Laravel Sanctum.

## Setup Instructions

### Prerequisites
- PHP 8.1+
- Composer
- MySQL 8.0+
- Redis (for caching)
- Laravel 10.x

### Installation

1. Clone the repository:
```bash
git clone https://github.com/jabbarSoomro/Translation-Management-Service
cd Translation-Management-Service
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

CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

5. Run migrations:
```bash
php artisan migrate
```

6. Seed database with test data (100k+ records):
```bash
php artisan translations:seed
```

## Authentication

This API uses token-based authentication with Laravel Sanctum. You must authenticate to access translation endpoints.

### Authentication Endpoints

#### 1. Register New User
```http
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

Response:
```json
{
    "message": "User registered successfully",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "access_token": "1|abc123...",
    "token_type": "Bearer"
}
```

#### 2. Login
```http
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

Response:
```json
{
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "access_token": "2|xyz789...",
    "token_type": "Bearer"
}
```

#### 3. Logout
```http
POST /api/logout
Authorization: Bearer {your_token}
```

#### 4. Get Current User Profile
```http
GET /api/me
Authorization: Bearer {your_token}
```

## API Endpoints

### Base URL
```
http://localhost:8000
```

**Note:** All translation endpoints require authentication. Include the token in the Authorization header:
```
Authorization: Bearer {your_token}
```

### Translation Endpoints

#### 1. Create Translation
```http
POST /api/translations
Authorization: Bearer {your_token}
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
Authorization: Bearer {your_token}
Content-Type: application/json

{
    "value": "Updated translation text",
    "tags": ["web"]
}
```

#### 3. Get Single Translation
```http
GET /api/translations/{id}
Authorization: Bearer {your_token}
```

#### 4. Search Translations
```http
GET /api/translations/search?key=welcome&locale=en&tags=web,mobile&content=application
Authorization: Bearer {your_token}
```

#### 5. List All Translations
```http
GET /api/translations?page=1&per_page=10
Authorization: Bearer {your_token}
```

#### 6. Delete Translation
```http
DELETE /api/translations/{id}
Authorization: Bearer {your_token}
```

#### 7. Export Translations (JSON for Frontend)
```http
GET /api/translations/export?locale=en&tags=web
Authorization: Bearer {your_token}
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

## Testing with Authentication

### Using Postman or cURL

1. First, register or login to get a token:
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

2. Copy the `access_token` from the response

3. Use the token for subsequent requests:
```bash
curl -X GET http://localhost:8000/api/translations \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Design Choices

### Security Features
- **Token-Based Authentication**: Laravel Sanctum provides secure API token authentication
- **Password Hashing**: Bcrypt hashing for all passwords
- **Token Expiration**: Configurable token expiration times
- **Middleware Protection**: All translation endpoints protected by auth middleware
- **CORS Support**: Configured for frontend integration

### Database Schema
- **translations table**: Stores translation records with indexed columns for fast lookups
- **tags table**: Normalized tag storage
- **translation_tag pivot table**: Many-to-many relationship between translations and tags
- **users table**: User authentication data
- **personal_access_tokens table**: Sanctum token storage
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
- **Authentication Layer**: Sanctum middleware for API protection

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

Run specific test suites:
```bash
php artisan test --filter=AuthTest
php artisan test --filter=TranslationTest
php artisan test --filter=PerformanceTest
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
│   │       ├── AuthController.php
│   │       └── TranslationController.php
│   ├── Requests/
│   │   ├── LoginRequest.php
│   │   ├── RegisterRequest.php
│   │   ├── StoreTranslationRequest.php
│   │   └── UpdateTranslationRequest.php
│   └── Resources/
│       ├── TranslationResource.php
│       └── TagResource.php
├── Models/
│   ├── Translation.php
│   ├── Tag.php
│   └── User.php
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
│   ├── TagFactory.php
│   └── UserFactory.php
└── migrations/
    ├── 2014_10_12_000000_create_users_table.php
    ├── 2019_12_14_000001_create_personal_access_tokens_table.php
    └── 2025_12_01_000001_create_translations_tables.php

routes/
└── api.php

tests/
├── Feature/
│   ├── AuthTest.php
│   ├── TranslationTest.php
│   └── PerformanceTest.php
└── Unit/
    └── TranslationServiceTest.php
```

## Security Best Practices Implemented

1. **Authentication Required**: All translation endpoints require valid authentication token
2. **Password Validation**: Minimum 8 characters with confirmation
3. **Token Management**: Old tokens deleted on new login
4. **SQL Injection Prevention**: Eloquent ORM and prepared statements
5. **Mass Assignment Protection**: Fillable properties defined on models
6. **HTTPS Ready**: Configure SSL in production
7. **Rate Limiting**: API rate limiting configured
8. **Input Validation**: Comprehensive validation on all inputs
