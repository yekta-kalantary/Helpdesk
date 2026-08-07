# مدل داده

## ERD سطح بالا

```text
users
  ├── model_has_roles -> roles -> role_has_permissions -> permissions
  ├── project_user -> projects
  ├── tasks.created_by / tasks.assigned_to
  ├── task_comments
  ├── tickets.created_by / tickets.assigned_to
  ├── ticket_messages
  └── notifications

customers
  ├── user_id -> users (Client Portal, nullable/unique)
  ├── projects
  └── tickets

projects
  ├── customer_id -> customers
  ├── project_user -> users
  ├── tasks
  └── tickets

tasks
  ├── project_id -> projects
  ├── task_comments
  └── media (polymorphic)

tickets
  ├── customer_id -> customers
  ├── project_id -> projects (nullable)
  └── ticket_messages -> media (polymorphic)

settings
```

## جداول اصلی

### `users`

کاربران داخلی و حساب‌های Portal مشتری در یک identity store قرار دارند. تشخیص نوع دسترسی از Role انجام می‌شود.

فیلد مهم: `is_active`.

### `customers`

- `user_id`: حساب Portal اختیاری و unique
- `status`: `lead | active | inactive`
- soft delete

### `projects`

- متعلق به یک Customer
- `type`: `website_design | seo | digital_marketing | support | other`
- `status`: `planning | active | paused | completed | cancelled`
- soft delete
- اعضای تیم از `project_user`

### `tasks`

- متعلق به یک Project
- assignee اختیاری
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
- حذف Customer در وضعیت وابستگی Project با FK محدود شده است؛ قبل از تغییر سیاست حذف، lifecycle پروژه‌ها بررسی شود.
- حذف Project باعث cascade شدن Taskهای فیزیکی در سطح FK فقط در delete واقعی می‌شود؛ در flow معمول Project soft-delete می‌شود.
- Portal user مشتری هنگام حذف/غیرفعال‌سازی Customer حذف نمی‌شود و فقط غیرفعال می‌شود تا audit/history از بین نرود.

## تغییر schema

برای تغییر schema موجود، migration جدید اضافه کنید و migrationهای قبلی را پس از انتشار production بازنویسی نکنید. migrationهای Settings با migrationهای Laravel متفاوت‌اند و در `app-modules/settings/database/settings` قرار می‌گیرند.
