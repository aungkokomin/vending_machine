# Vending Machine PHP/MySQL Application

This is a PHP 8.1+ vending machine system using MySQL, PDO, PHP sessions, role-based access control, product CRUD, purchase transactions, REST API routes, and token-based API authentication.

## GitHub Repository

- Repository: https://github.com/aungkokomin/vending_machine.git
- Main branch: `main`

Clone the project:

```bash
git clone https://github.com/aungkokomin/vending_machine.git
cd vending_machine
```

## Domain And Credentials

Local development domains:

- PHP built-in server: `http://localhost:8000`
- Docker server: `http://localhost:8080`

Production domain:

- Replace with your real domain after deployment, for example `https://your-domain.com`

Seeded login credentials:

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@example.com` | `password` |
| User | `user@example.com` | `password` |

Change these passwords before using the project in production.

## Project Structure

```text
config/                 Application configuration and .env loading
database/               SQL schema and PHP seed files
database/migrations/    SQL migration files for each table
docker/                 Apache and MySQL Docker configuration
public/                 Web document root
src/Auth/               Session auth and role guard
src/Controllers/        Web and API controllers
src/Database/           PDO connection class
src/Http/               Router, route attributes, JSON response helper
src/Repositories/       PDO repositories and interfaces
src/Services/           Purchase service
src/Support/            View, validation, redirect, JWT helpers
tests/                  PHPUnit controller tests
views/                  PHP templates
```

## Setup Without Docker

1. Copy the environment file:

```bash
copy .env.example .env
```

2. Update `.env` with your MySQL credentials:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=vending_machine
DB_USER=root
DB_PASS=
JWT_SECRET=change-this-secret
APP_ENV=local
```

3. Run database migrations:

```bash
php database/migrate.php
```

4. Run the PHP database seed:

```bash
php database/02-seed.php
```

5. Start the PHP development server:

```bash
php -S localhost:8000 -t public
```

6. Open:

```text
http://localhost:8000
```

## Setup With Docker

Build and start the PHP/Apache app, MySQL database, and seed runner:

```bash
docker compose up --build -d
```

Open:

```text
http://localhost:8080
```

Run the seed manually again if needed:

```bash
docker compose run --rm seed
```

Stop the containers:

```bash
docker compose down
```

## Server Deployment With Docker

1. Install Docker and Docker Compose on your server.

2. Clone the repository:

```bash
git clone https://github.com/aungkokomin/vending_machine.git
cd vending_machine
```

3. Edit production secrets in `docker-compose.prod.yml`:

```yaml
DB_PASS: change-this-db-password
MYSQL_PASSWORD: change-this-db-password
MYSQL_ROOT_PASSWORD: change-this-root-password
JWT_SECRET: change-this-long-random-secret
```

4. Start the production containers:

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml up --build -d
```

5. Point your domain DNS `A` record to the server IP address.

6. Visit your production domain in the browser.

Useful deployment commands:

```bash
docker compose ps
docker compose logs -f app
docker compose logs -f db
docker compose down
```

## Database

The database has three main tables:

- `users`: stores name, email, password hash, and role.
- `products`: stores vending machine products, price, available quantity, and `deleted_at` for soft deletes.
- `transactions`: stores purchase history and links each purchase to a user and product.

Relationships:

- `transactions.user_id` references `users.id`
- `transactions.product_id` references `products.id`

Database files:

- `database/migrate.php`: runs all migration files in order.
- `database/migrations/001_create_database.sql`: creates the database.
- `database/migrations/002_create_users_table.sql`: creates the `users` table.
- `database/migrations/003_create_products_table.sql`: creates the `products` table with soft delete support.
- `database/migrations/004_create_transactions_table.sql`: creates the `transactions` table.
- `database/schema.sql`: full schema snapshot.
- `database/seed.php`: seeds users and sample products.
- `database/02-seed.php`: wrapper file for running the seed.

## Web Routes

| Method | Route | Description |
| --- | --- | --- |
| GET | `/products` | Product list with pagination and sorting |
| GET | `/products/create` | Admin-only product create form |
| POST | `/products` | Admin-only product create action |
| GET | `/products/{id}/edit` | Admin-only product edit form |
| POST | `/products/{id}/update` | Admin-only product update action |
| POST | `/products/{id}/delete` | Admin-only product delete action |
| GET | `/products/{id}/purchase` | Logged-in product purchase form |
| POST | `/products/{id}/purchase` | Logged-in product purchase action |
| GET | `/login` | Login form |
| POST | `/login` | Login action |
| POST | `/logout` | Logout action |

## API Routes

| Method | Route | Description |
| --- | --- | --- |
| POST | `/api/token` | Get API token |
| GET | `/api/products` | List products |
| GET | `/api/products/{id}` | Show one product |
| POST | `/api/products` | Admin-only create product |
| POST | `/api/products/{id}` | Admin-only update product |
| DELETE | `/api/products/{id}` | Admin-only delete product |
| POST | `/api/products/{id}/purchase` | Token-authenticated purchase |

Protected API requests must include:

```http
Authorization: Bearer your.jwt.token
```

## Testing

Install development dependencies:

```bash
composer install
```

Run tests:

```bash
composer test
```

## Notes

- The Apache Docker container serves `public/` as the document root.
- Private source files in `src/`, `config/`, and `views/` are not directly web-accessible.
- Admin-only pages are protected by the role stored in the PHP session.
- Product input is validated on both the server side and through HTML form validation.
