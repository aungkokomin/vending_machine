# Vending Machine PHP/MySQL Application

This project is a small PHP 8.1+ MVC-style vending machine app using MySQL, PDO, sessions, role-based access control, product purchasing, PHPUnit tests, and a REST API protected with JWT bearer tokens.

## Database Setup

1. Create a MySQL database by running `database/schema.sql`.
2. Copy `.env.example` to `.env`.
3. Update the database credentials in `.env`.
4. Run `composer install`.
5. Start the app with:

```bash
php -S localhost:8000 -t public
```

The schema has three main tables:

- `users`: stores account details, `password_hash`, and `role` (`Admin` or `User`).
- `products`: stores vending items with name, description, positive price, and available quantity.
- `transactions`: records each purchase and links to `users.id` and `products.id` through foreign keys.

The included admin seed uses:

- Email: `admin@example.com`
- Password: `password`

## Web Routes

- `GET /products`: list products with pagination and sorting.
- `GET /products/create`: admin-only create form.
- `POST /products`: admin-only create action.
- `GET /products/{id}/edit`: admin-only edit form.
- `POST /products/{id}/update`: admin-only update action.
- `POST /products/{id}/delete`: admin-only delete action.
- `GET /products/{id}/purchase`: logged-in purchase form, registered through attribute routing.
- `POST /products/{id}/purchase`: logged-in purchase action, registered through attribute routing.

## API Routes

- `POST /api/token`: returns a JWT for valid credentials.
- `GET /api/products`: list products.
- `GET /api/products/{id}`: show one product.
- `POST /api/products`: admin-only create product.
- `POST /api/products/{id}`: admin-only update product.
- `DELETE /api/products/{id}`: admin-only delete product.
- `POST /api/products/{id}/purchase`: token-authenticated product purchase.

Send protected API requests with:

```http
Authorization: Bearer your.jwt.token
```

## Testing

Install development dependencies, then run:

```bash
composer test
```
