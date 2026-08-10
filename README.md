# Helpdesk

یک سامانه ساده مدیریت پروژه و تسک با Laravel 13 و Livewire 4.

## ساختار سیستم

فقط سه بخش کاربردی وجود دارد:

```text
Users
Projects
Tasks
```

### Users

ادمین می‌تواند کاربر بسازد و ویرایش کند. اطلاعات کاربر مستقیماً روی جدول `users` نگهداری می‌شود و Role/Permission و Contact جداگانه وجود ندارد.

فیلدهای اصلی:

```text
name
last_name
email
mobile
password
is_active
is_admin
```

### Projects

هر پروژه فقط عنوان، توضیح و اعضا دارد. ارتباط کاربر و پروژه در جدول `project_user` نگهداری می‌شود.

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

هر Task فقط متعلق به یک Project است:

```text
tasks
  id
  project_id
  title
  description
  is_done
```

کاربر عادی فقط Taskهای پروژه‌هایی را می‌بیند که در آن‌ها عضو است. ادمین همه Taskها را می‌بیند و مدیریت می‌کند.

## مسیرها

```text
/projects
/tasks
/users   # فقط ادمین
```

بخش‌های Contact، Customer، Role/Permission، Ticket، Report، Setting، Media، Attachment و ساختارهای CRM در UI و جریان اصلی سیستم وجود ندارند.

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

برای دیتابیس موجود:

```bash
composer install
php artisan optimize:clear
php artisan migrate --force
php artisan db:seed --force
```

Migration نهایی داده‌های User و Task موجود را به ساختار ساده جدید منتقل می‌کند و جدول‌ها و ستون‌های legacy را حذف می‌کند.

## بررسی کیفیت

```bash
composer validate --no-check-publish
php artisan migrate:fresh --force
php artisan db:seed --force
php artisan route:list
php artisan view:cache
php artisan test
./vendor/bin/pint --test
npm run build
```
