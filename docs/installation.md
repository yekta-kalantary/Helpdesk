# نصب و ارتقا

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

Migrationهای اصلی مستقیماً جدول‌های ساده `users`، `projects`، `project_user` و `tasks` را می‌سازند.

## ارتقا از نسخه قبلی

برای دیتابیس موجود از `migrate:fresh` استفاده نکنید:

```bash
composer install
php artisan optimize:clear
php artisan migrate --force
php artisan db:seed --force
```

Migration ارتقا اطلاعات مورد نیاز User و Task را منتقل می‌کند و schemaهای legacy را حذف می‌کند.

قبل از migration روی production از دیتابیس backup بگیرید.

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
