# Helpdesk

یک سامانه ساده مدیریت پروژه و تسک با Laravel و Livewire.

## ساختار سیستم

فقط سه بخش کاربردی وجود دارد:

```text
Users
Projects
Tasks
```

### Users

ادمین می‌تواند کاربر بسازد و ویرایش کند.

```text
users
  id
  name
  last_name
  email
  mobile
  password
  is_active
  is_admin
```

### Projects

هر پروژه فقط عنوان، توضیح و اعضا دارد.

```text
projects
  id
  title
  description

project_user
  project_id
  user_id
```

ادمین همه پروژه‌ها را می‌بیند. کاربر عادی فقط پروژه‌هایی را می‌بیند که عضو آن‌هاست.

### Tasks

هر Task فقط متعلق به یک Project است.

```text
tasks
  id
  project_id
  title
  description
  is_done
```

کاربر عادی فقط Taskهای پروژه‌هایی را می‌بیند که عضو آن‌هاست. ادمین همه Taskها را می‌بیند و مدیریت می‌کند.

## مسیرها

```text
/projects
/tasks
/users   # فقط ادمین
```

## نصب

نیازمندی‌ها:

- PHP 8.4+
- Composer 2
- MariaDB 11.x
- Node.js 22

```bash
cp .env.example .env
composer run setup
php artisan serve
```

این نسخه schema قبلی پروژه را پشتیبانی نمی‌کند. هنگام مهاجرت از نسخه قبلی، دیتابیس توسعه را یک‌بار از نو بسازید:

```bash
php artisan migrate:fresh --seed
```

## بررسی کیفیت

```bash
composer validate --no-check-publish
php artisan migrate:fresh --force
php artisan db:seed --force
php artisan route:list
php artisan view:cache
php artisan test
./vendor/bin/pint --test
npm ci
npm run build
```
