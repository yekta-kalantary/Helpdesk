# مدل داده

## ERD سطح بالا

```text
people
  ├── users (0..1, account/login)
  └── customers (0..1, only when type=customer)

users
  ├── model_has_roles -> roles -> role_has_permissions -> permissions
  ├── project_user -> projects
  ├── tasks.created_by / tasks.assigned_to
  ├── task_comments
  ├── tickets.created_by / tickets.assigned_to
  ├── ticket_messages
  └── notifications

customers
  ├── person_id -> people (unique)
  ├── projects
  └── tickets

projects
  ├── customer_id -> customers
  ├── project_user -> users
  ├── tasks
  └── tickets

settings
```

## منبع حقیقت اطلاعات افراد

`people` تنها منبع اطلاعات هویتی است:

- `type`: `customer | employee`
- `first_name`
- `last_name`
- `email`
- `mobile`

نام، ایمیل و موبایل در `users` یا `customers` تکرار نمی‌شوند.

### `users`

`users` فقط حساب احراز هویت و مجوزدهی است و با `person_id` به `people` متصل می‌شود.

- Employee باید User داشته باشد.
- Customer می‌تواند User نداشته باشد.
- ایجاد User برای Customer فقط دسترسی Portal را فعال می‌کند و هویت Customer را تغییر نمی‌دهد.
- Role فقط Authorization است؛ Customer/Employee بودن از `people.type` و رابطه دامنه تعیین می‌شود.

### `customers`

`customers` اطلاعات مخصوص رابطه مشتری را نگه می‌دارد:

- `person_id`: یکتا و متعلق به Person از نوع `customer`
- `status`: `lead | active | inactive`
- `notes`
- soft delete

حساب Portal از `users.person_id = customers.person_id` resolve می‌شود و FK مستقیمی از Customer به User وجود ندارد.

### `projects`

- متعلق به یک Customer
- `type`: `website_design | seo | digital_marketing | support | other`
- `status`: `planning | active | paused | completed | cancelled`
- soft delete
- اعضای تیم از `project_user`

### `tasks`

- متعلق به یک Project
- assignee اختیاری و باید Employee فعال باشد
- creator اجباری
- `priority`: `low | medium | high | urgent`
- `status`: `todo | in_progress | review | done | cancelled`
- `is_customer_visible`: پیش‌فرض `false`
- زمان تخمینی و مصرف‌شده برحسب دقیقه
- soft delete

`is_customer_visible` boundary اطلاعات داخلی و Client Portal است. همه queryهای مشتری باید آن را enforce کنند.

### `task_comments`

کامنت داخلی تیم است و در Client Portal نمایش داده نمی‌شود.

### `tickets`

- متعلق به Customer
- Project اختیاری
- creator و assignee
- assignee باید Employee فعال باشد
- category، priority، status
- soft delete

### `ticket_messages`

Thread تیکت به‌صورت messageهای مستقل ذخیره می‌شود. هر message می‌تواند media داشته باشد.

### `media`

جدول استاندارد Media Library برای attachmentهای Task و TicketMessage. فایل فیزیکی روی disk `local` است.

### `notifications`

Database notification استاندارد Laravel.

### `settings`

Repository پکیج `spatie/laravel-settings`. رمز SMTP encrypted setting است.

## قواعد حذف

- Customer و Project و Task و Ticket soft delete دارند.
- حذف Customer حساب Portal را حذف نمی‌کند؛ حساب همان Person غیرفعال می‌شود تا audit/history باقی بماند.
- حذف Employee از مسیر مدیریت کاربران، User و Person همان Employee را در یک transaction حذف می‌کند.
- قبل از تغییر سیاست حذف، وابستگی Project/Task/Ticket بررسی شود.

## تغییر schema

برای نصب‌های موجود migration سازگاری داده‌ها را از ستون‌های legacy به `people` منتقل می‌کند و سپس ستون‌های تکراری را حذف می‌کند. این migration forward-only است؛ rollback نباید دوباره دو source of truth ایجاد کند.
