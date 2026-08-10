# مدل داده

## contacts

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

## users

حساب ورود اختیاری برای Contact:

```text
id
contact_id (unique)
email_verified_at
password
is_active
remember_token
```

## projects

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

## tasks

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

Task comment و attachment همچنان بخشی از Tasks هستند. هیچ جدول Customer، Ticket، Notification یا Settings در baseline جدید ساخته نمی‌شود.
