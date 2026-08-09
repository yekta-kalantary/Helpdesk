# نصب

## نیازمندی‌ها

- PHP 8.4+
- Composer 2
- MariaDB 11.x
- Node.js 22 برای build frontend

ابتدا دیتابیس `helpdesk` را ایجاد کنید و مقادیر `.env` را تنظیم کنید.

```bash
cp .env.example .env
composer run setup
php artisan serve
```

برای تست:

```bash
php artisan migrate:fresh --force
php artisan db:seed --force
php artisan test
./vendor/bin/pint --test
npm ci
npm run build
```

`migrate:fresh` baseline جدید را با Contacts، Users، Projects و Tasks ایجاد می‌کند.
