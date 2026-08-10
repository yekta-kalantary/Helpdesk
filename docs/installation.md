# نصب

## نیازمندی‌ها

- PHP 8.4+
- Composer 2
- MariaDB 11.x
- Node.js 22

## نصب تازه

ابتدا دیتابیس را ایجاد و `.env` را تنظیم کنید:

```bash
cp .env.example .env
composer run setup
php artisan serve
```

Migrationها مستقیماً جدول‌های `users`، `projects`، `project_user` و `tasks` را می‌سازند.

اگر پروژه را از ساختار توسعه قبلی جایگزین می‌کنید، دیتابیس را یک‌بار از نو بسازید:

```bash
php artisan migrate:fresh --seed
```

## تست کامل

```bash
php artisan migrate:fresh --force
php artisan db:seed --force
php artisan route:list
php artisan view:cache
php artisan test
./vendor/bin/pint --test
npm ci
npm run build
```
