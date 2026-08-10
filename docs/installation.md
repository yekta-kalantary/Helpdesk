# نصب و ارتقا

## نیازمندی‌ها

- PHP 8.4+
- Composer 2
- MariaDB 11.x
- Node.js 22 برای build frontend

## نصب تازه

ابتدا دیتابیس `helpdesk` را ایجاد و `.env` را تنظیم کنید:

```bash
cp .env.example .env
composer run setup
php artisan serve
```

Schema تازه از migrationهای module-local ساخته می‌شود: Contacts، Identity، Projects، Tasks و Media هرکدام schema متعلق به خودشان را load می‌کنند.

## ارتقا از نسخه قبلی

برای دیتابیس قبلاً migrate‌شده از `migrate:fresh` استفاده نکنید. بعد از دریافت کد جدید:

```bash
composer install
php artisan optimize:clear
php artisan migrate --force
php artisan db:seed --force
```

Forward migrationها داده‌های `people/customers` را به Contacts منتقل می‌کنند، FKهای User/Project را عوض می‌کنند و فقط بعد از backfill موفق schemaهای حذف‌شده را پاک می‌کنند.

قبل از اجرای production migration از دیتابیس backup بگیرید.

## تست کامل

```bash
php artisan migrate:fresh --force
php artisan db:seed --force
php artisan test
./vendor/bin/pint --test
npm ci
npm run build
```

CI علاوه بر fresh schema، یک schema legacy نماینده می‌سازد و `php artisan migrate` را روی آن اجرا می‌کند تا upgrade path نیز regression-test شود.
