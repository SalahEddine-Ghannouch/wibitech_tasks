# Laravel 10 Task Management API

A RESTful API built with Laravel 10 that implements role-based access control for task management.

## Features

- User authentication with Laravel Sanctum
- Role-based access control (Admin and Regular User roles)
- CRUD operations for tasks
- Admin can manage all tasks and users
- Regular users can only view and update their own tasks

## Requirements

- PHP >= 8.1
- Composer
- MySQL
- Laravel 10

## Installation

1. Clone the repository:

```bash
git clone <repository-url>
cd tasks
```

2. Install dependencies:

```bash
composer install
```

3. Copy the environment file:

```bash
cp .env.example .env
```

4. Generate application key:

```bash
php artisan key:generate
```

5. Configure your database in `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

6. Run migrations and seed the database:

```bash
php artisan migrate --seed
```

7. Start the development server:

```bash
php artisan serve
```

## Seeded Users

The database is seeded with two users:

1. Admin User:

   - Email: admin@example.com
   - Password: password
   - Role: admin
2. Regular User:

   - Email: user@example.com
   - Password: password
   - Role: user

## API Endpoints

### Authentication

All endpoints require authentication using Laravel Sanctum. Include the token in the Authorization header:

```
Authorization: Bearer <your-token>
```

### Tasks

#### List Tasks

- **GET** `/api/tasks`
- Admin: sees all tasks
- Regular user: sees only their tasks

#### Get Single Task

- **GET** `/api/tasks/{id}`
- Admin: can view any task
- Regular user: can only view their own tasks

#### Create Task

- **POST** `/api/tasks`
- Admin only
- Required fields:
  - title
  - description
  - status (in_progress or done)
  - user_id

#### Update Task

- **PUT** `/api/tasks/{id}`
- Admin: can update any task
- Regular user: can only update their own tasks

#### Delete Task

- **DELETE** `/api/tasks/{id}`
- Admin only

### Users

#### List Users

- **GET** `/api/users`
- Admin only

## Error Responses

The API returns appropriate HTTP status codes:

- 200: Success
- 201: Created
- 204: No Content
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 422: Validation Error
- 500: Server Error

## Security

- All endpoints are protected by authentication
- Role-based access control is implemented
- Input validation is enforced
- CSRF protection is enabled
- Passwords are hashed

## Testing

The application includes comprehensive test suites for both unit and feature testing using Laravel's built-in testing framework.

### Running Tests

To run all tests:

```bash
php artisan test
```

To run specific test suites:

```bash
# Run only unit tests
php artisan test --testsuite=Unit

# Run only feature tests
php artisan test --testsuite=Feature
```

### Test Suites

#### Unit Tests

- User model tests (role checking, relationships)
- Task model tests (relationships, attribute casting)
- Validation rules tests

#### Feature Tests

- Authentication tests
  - Login
  - Token validation
  - Role-based access
- Task management tests
  - CRUD operations
  - Authorization rules
  - Validation rules
- User management tests
  - User listing (admin only)
  - User creation
  - Role assignment

