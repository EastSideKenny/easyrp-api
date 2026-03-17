# EasyRP API

> From invoice to online store, simplified.

A Laravel 11 REST API application with PostgreSQL, Redis, and Laravel Sanctum token-based authentication.

## Tech Stack

- **Framework:** Laravel 11 (PHP 8.3+)
- **Database:** PostgreSQL 16
- **Cache / Session / Queue:** Redis 7
- **Authentication:** Laravel Sanctum (token-based API auth)

## Requirements

- PHP >= 8.3
- Composer
- PostgreSQL >= 16
- Redis >= 7

## Getting Started

### 1. Clone and install dependencies

```bash
git clone https://github.com/EastSideKenny/easyrp-api.git
cd easyrp-api
composer install
```

### 2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and update the database and Redis credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=easyrp
DB_USERNAME=easyrp
DB_PASSWORD=secret

REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 3. Run database migrations

```bash
php artisan migrate
```

### 4. Start the development server

```bash
php artisan serve
```

## Docker (recommended for local dev)

Start all services (app, nginx, PostgreSQL, Redis) with Docker Compose:

```bash
cp .env.example .env
docker-compose up -d
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
```

The API will be available at `http://localhost:8000`.

## API Endpoints

### Authentication

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| `POST` | `/api/register` | Register a new user | No |
| `POST` | `/api/login` | Login and get a token | No |
| `POST` | `/api/logout` | Logout (revoke token) | Yes |
| `GET`  | `/api/user` | Get authenticated user | Yes |

### Register

```bash
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

### Login

```bash
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password"
}
```

Response:
```json
{
  "user": { "id": 1, "name": "John Doe", "email": "john@example.com" },
  "token": "1|abc123...",
  "token_type": "Bearer"
}
```

### Authenticated requests

Pass the token as a Bearer token in the `Authorization` header:

```bash
GET /api/user
Authorization: Bearer 1|abc123...
```

## Running Tests

```bash
php artisan test
```

## License

The EasyRP API is open-sourced software licensed under the [MIT license](LICENSE).

