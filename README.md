# Financial Dashboard System

A web-based financial dashboard system that enables users to track transactions, view financial analytics, and access customized reports with role-based access control.

## Features

- **User Management**: Role-based system (Admin, Manager, User) with different permissions
- **Transaction Management**: Track income, expenses, and investments with categorization
- **Data Visualization**: Charts and graphs for financial analysis 
- **Tags**: Tag-based organization for transactions

## Tech Stack

- Backend: Laravel 12
- Database: MySQL and SQLite (for testing)
- Repository Pattern + Service Layer Architecture
- API-first approach for easy frontend integration

## Requirements

- PHP 8.4+
- Composer
- Database (MySQL)

## Installation

### Method 1: Standard Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/financial-dashboard.git
   cd financial-dashboard
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

5. Configure your database connection in the `.env` file:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=financial_dashboard
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   Or for SQLite (simpler setup):
   ```
   DB_CONNECTION=sqlite
   ```
   Then create the database file:
   ```bash
   touch database/database.sqlite
   ```

6. Run migrations and seed the database:
   ```bash
   php artisan migrate --seed
   ```

7. Start the development server:
   ```bash
   php artisan serve
   ```

### Method 2: Using Docker

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/financial-dashboard.git
   cd financial-dashboard
   ```

2. Build and start the containers:
   ```bash
   docker compose up -d
   ```

3. Run migrations and seed the database:
   ```bash
   docker compose exec app php artisan migrate --seed
   ```

4. Generate application key:
   ```bash
   docker compose exec app php artisan key:generate
   ```

5. Access the application at `http://localhost:8000`

## User Roles and Permissions

### Administrator
- Complete system access
- Can manage all users, categories, and reports
- Can delete or deactivate any account
- Can reset passwords for any user

### Manager
- Can register and manage users
- Can create reports for users
- Cannot register new managers or administrators
- Cannot reset passwords

### User
- Can register transactions
- Can create custom categories
- Can apply tags to transactions
- Can view their financial data

## API Routes

The system uses a RESTful API structure:

- **Authentication**: `/api/login`, `/api/logout`
- **Transactions**: `/api/transactions`
- **Categories**: `/api/categories`
- **Tags**: `/api/tags`
- **Users**: `/api/users` (Admin/Manager only)
- **Dashboard**: `/api/dashboard`

### Sample API Calls

#### Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@example.com", "password": "password"}'
```

#### Get Transactions
```bash
curl -X GET http://localhost:8000/api/transactions \
  -H "Authorization: Bearer {your_token}"
```

#### Create Transaction
```bash
curl -X POST http://localhost:8000/api/transactions \
  -H "Authorization: Bearer {your_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 100.50,
    "date": "2025-03-24",
    "description": "Grocery shopping",
    "payment_method": "Credit Card",
    "category_id": 1,
    "tags": ["food", "essentials"]
  }'
```

## Testing

Run the test suite with:

```bash
php artisan test
```

### Creating Test Cases

1. Create a new test:
   ```bash
   php artisan make:test FeatureNameTest
   ```

2. Write your test using the Laravel testing framework.

3. Run specific tests:
   ```bash
   php artisan test --filter=TestClassName
   ```

## Default Login

After seeding the database, you can log in with:

- **Email**: admin@example.com
- **Password**: password

## Directory Structure

- `app/Http/Controllers/Api` - API controllers
- `app/Models` - Database models
- `app/Repositories` - Repository pattern implementation
- `app/Services` - Service layer
- `app/Policies` - Authorization policies
- `database/migrations` - Database structure

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature-name`
3. Commit your changes: `git commit -m 'Add feature'`
4. Push to the branch: `git push origin feature-name`
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.
