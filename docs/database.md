# مدل داده

## Contacts module

### contacts

منبع واحد اطلاعات شخصی:

```text
id
first_name
last_name
gender
email
mobile
province
city
address
postal_code
created_at
updated_at
```

Migrationهای `contacts` داخل `app-modules/contacts/database/migrations` هستند.

## Identity module

### users

حساب ورود اختیاری برای Contact:

```text
id
contact_id (unique)
email_verified_at
password
is_active
remember_token
```

Role/Permission tables نیز متعلق به Identity هستند. `users.contact_id` به Contacts اشاره می‌کند، اما پروفایل شخصی داخل users تکرار نمی‌شود.

## Projects module

### projects

```text
id
contact_id (nullable)
category: contact | internal
title
type
status
description
starts_at
ends_at
```

Project نوع `contact` باید Contact داشته باشد؛ Project نوع `internal` بدون Contact است.

### project_user

عضویت کارکنان در پروژه را نگه می‌دارد.

## Tasks module

### tasks

```text
id
project_id
assigned_to (nullable)
created_by
title
description
priority
status
due_at
estimated_minutes
spent_minutes
```

### task_comments

کامنت‌های داخلی Task را نگه می‌دارد. `assigned_to` در سطح application باید User فعال و عضو همان Project باشد.

## Media module

### media

جدول polymorphic عمومی فایل‌ها متعلق به module `Media` است، نه Tasks. Business moduleها collection و authorization خود را تعریف می‌کنند و storage/read/delete را از `MediaManager` می‌گیرند.

Task در حال حاضر collection زیر را استفاده می‌کند:

```text
attachments
```

همین زیرساخت می‌تواند بدون dependency به Tasks برای Project/Contact collectionهای دیگر استفاده شود.

## Upgrade از schema قدیمی

برای دیتابیس‌های قبلی `migrate:fresh` لازم نیست. Forward migrationهای جدید این مسیر را اجرا می‌کنند:

```text
people → contacts
users.person_id → users.contact_id
projects.customer_id → projects.contact_id
project.category: customer → contact
Spatie model_type: App\Models\User → Modules\Identity\Infrastructure\Models\User
```

بعد از صحت backfill، schemaهای حذف‌شده پاک می‌شوند:

```text
people
customers
tickets
ticket_messages
notifications
settings
tasks.is_customer_visible
```

هیچ migration اجراشده‌ای برای این upgrade rewrite نمی‌شود.
