# MISR — Professional Learning Platform

MISR is a professional learning and skills platform organized around **Learn → Practice → Build → Certify → Work → Grow**. It preserves the proven authentication, course progress, assignment/submission, review, messaging and certificate foundations of the existing application.

## Stack

- Laravel 10 / PHP 8.1+
- React 19 with Vite 4
- Laravel Sanctum authentication
- Database-backed roles, permissions and learning relationships

## Local setup

1. Copy `.env.example` to `.env` and configure the database and mail provider.
2. Run `composer install` and `npm install`.
3. Run `php artisan key:generate` and `php artisan migrate --seed`.
4. Start Laravel with `php artisan serve` and Vite with `npm run dev`.

Production frontend assets are generated with `npm run build`. Backend tests run with `php artisan test`.

## Product scope

Phase 1 prioritizes the public learning experience, course marketplace, course details/player, projects, opportunities, mentorship and live-class foundations, professional certificates, role-specific dashboards, and Ministry-ready presentation quality. Payments, full opportunity applications, institutional tenancy, advanced mentorship scheduling and government integrations are Phase 2.

See [the transformation audit](docs/PLATFORM_TRANSFORMATION_AUDIT.md) for the keep/modify/remove mapping and implementation boundary.

No Docker configuration or Git remote is included. Initialize the intended repository separately when ready.
