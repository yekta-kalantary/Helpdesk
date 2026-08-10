# مدل داده

دیتابیس اصلی برنامه فقط چهار جدول domain دارد:

```text
users
projects
project_user
tasks
```

جدول‌های framework مثل `sessions`، `cache`، `jobs` و `migrations` جدا هستند.

## users

```text
id
name
last_name
email
mobile
email_verified_at
password
is_active
is_admin
remember_token
created_at
updated_at
```

`is_admin` تنها فلگ مدیریتی سیستم است.

## projects

```text
id
title
description
created_at
updated_at
```

## project_user

```text
project_id
user_id
created_at
updated_at
```

این جدول مشخص می‌کند هر کاربر عضو چه پروژه‌هایی است.

## tasks

```text
id
project_id
title
description
is_done
created_at
updated_at
```

Task فقط به Project تعلق دارد. کاربر عادی Task را زمانی می‌بیند که عضو همان Project باشد.

## سیاست schema

این نسخه فقط همین schema را پشتیبانی می‌کند. برای جایگزینی نسخه‌های توسعه قبلی:

```bash
php artisan migrate:fresh --seed
```
