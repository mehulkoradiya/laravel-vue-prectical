# Stock Management System

A full-stack stock management application built with Laravel 12 (Backend) and Vue 3 (Frontend).

## Features

### Backend (Laravel)
- **Token-based Authentication** using Laravel Passport
- **Stock Management APIs** with server-side pagination and search
- **Bulk Stock Entry** support
- **Store Management** for multiple store locations
- **Automated Scheduler** to update stock status daily at midnight
- **MySQL Database** with proper relationships

### Frontend (Vue 3)
- **Modern UI** with responsive design
- **Vuex Store** for state management and token storage
- **Vue Router** with authentication guards
- **Tabulator** for stock listing with search and pagination
- **AG Grid** for bulk stock entry with editable cells
- **Axios** for API communication

## Prerequisites

- PHP 8.2+ with required extensions (sodium, pdo_mysql, openssl)
- MySQL 5.7+
- Node.js 16+
- Composer
- Laragon (recommended for Windows)

## Installation

### 1. Clone the Repository
```bash
git clone https://github.com/mehulkoradiya/laravel-vue-prectical.git
cd laravel-vue-prectical
```

### 2. Backend Setup (Laravel)

```bash
cd backend

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=stock_mgmt
DB_USERNAME=root
DB_PASSWORD=

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed sample data
php artisan db:seed

# Start development server
php artisan serve
```

### 3. Frontend Setup (Vue 3)

```bash
cd frontend

# Install dependencies
npm install

# Start development server
npm run dev
```

## API Endpoints

### Authentication
- `POST /api/login` - User login
- `POST /api/logout` - User logout (requires auth)

### Stores
- `GET /api/stores` - List all active stores

### Stocks
- `GET /api/stocks` - List stocks with pagination, search, and sorting
- `POST /api/stocks/bulk` - Create multiple stocks at once
- `DELETE /api/stocks/{id}` - Delete a specific stock

## Database Schema

### Stores Table
- `id` - Primary key
- `name` - Store name
- `location` - Store location
- `is_active` - Store status
- `created_at`, `updated_at` - Timestamps

### Stocks Table
- `id` - Primary key
- `stock_no` - Unique stock number (auto-generated)
- `item_code` - Item identifier
- `item_name` - Item description
- `quantity` - Stock quantity
- `location` - Storage location
- `store_id` - Foreign key to stores table
- `in_stock_date` - Date when stock becomes available
- `status` - Stock status (pending/in_stock)
- `created_at`, `updated_at` - Timestamps

## Scheduler

The system includes a daily scheduler that runs at midnight to update stock status:

```bash
# Manual execution
php artisan stocks:update-in-stock-status

# The scheduler runs automatically at 00:00 daily
```

## Usage

1. **Login** - Use any valid email/password combination
2. **Dashboard** - Navigate between different sections
3. **Stock List** - View, search, and delete stock entries
4. **Bulk Entry** - Add multiple stocks using the grid interface

## Development

### Backend
- Laravel 12 with modern routing
- Passport for API authentication
- Eloquent ORM with relationships
- Database migrations and seeders

### Frontend
- Vue 3 Composition API
- Vite for fast development
- Vuex 4 for state management
- Vue Router 4 for navigation
- Tabulator for data tables
- AG Grid for editable grids


