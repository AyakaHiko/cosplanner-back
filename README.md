# 🎭 Cosplanner Backend API

This is the Laravel-based backend for the Cosplanner application. It provides a RESTful API for project management, image handling, and user authentication.

## 🛠 Tech Stack

* **Framework:** [Laravel 10](https://laravel.com/)
* **Language:** PHP 8.2
* **Database:** PostgreSQL
* **Caching & Queues:** Redis / RabbitMQ
* **File Storage:** AWS S3 (via Flysystem)
* **Authentication:** 
    * [JWT Auth](https://github.com/PHP-Open-Source-Saver/jwt-auth) (Stateless authentication)
    * [Laravel Socialite](https://laravel.com/docs/10.x/socialite) (Google OAuth)
* **Image Processing:** [Intervention Image v3](https://image.intervention.io/)

## 🚀 Key Features

* **RESTful API:** Clean and structured endpoints for all resources.
* **JWT Authentication:** Secure, stateless user sessions.
* **Advanced Image Management:**
    * Automatic thumbnail generation and cropping.
    * Integrated with AWS S3 for scalable storage.
    * Album-based organization for cosplay projects.
* **Project Tracking:** Manage materials, progress, and deadlines.
* **Multi-stage Planning:** Organize cosplays into projects with nested albums and assets.

## 📁 Project Structure

* `app/Http/Controllers` — API logic and request handling.
* `app/Models` — Eloquent models and relationships (Cosplan, Album, Material, etc.).
* `app/Services` — Business logic encapsulation (e.g., `ImageService`).
* `database/migrations` — Database schema definitions.
* `routes/api.php` — API route definitions.

## ⚙️ Core Components

### Authentication
The backend uses **JWT (JSON Web Tokens)** for stateless authentication.
* `POST /api/register` — Create a new account.
* `POST /api/login` — Obtain a JWT token.
* `GET /api/user` — Fetch authenticated user details.

### Image Service
A dedicated `ImageService` handles:
* Uploading images to AWS S3.
* Deleting original and associated files.
* Generating unique filenames for stored assets.

## 🛠 Installation & Setup

1. **Install Dependencies:**
   ```bash
   composer install
   ```

2. **Environment Configuration:**
   Copy `.env.example` to `.env` and configure your database, AWS, and JWT settings.
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan jwt:secret
   ```

3. **Database Migration:**
   ```bash
   php artisan migrate
   ```

## 🧪 Testing

Run PHPUnit tests:
```bash
php artisan test
```

---
*Developed with Laravel 10.*
