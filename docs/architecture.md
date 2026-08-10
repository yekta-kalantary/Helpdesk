# معماری

پروژه فقط سه بخش کاربردی دارد:

```text
Users
Projects
Tasks
```

کد برای جداسازی مسئولیت‌ها در سه ماژول نگهداری می‌شود:

```text
Identity   -> کاربران و ورود
Projects   -> پروژه‌ها و اعضای پروژه
Tasks      -> تسک‌های پروژه
```

## وابستگی‌ها

```text
Identity
   ↓
Projects
   ↓
Tasks
```

`Projects` برای انتخاب اعضا از User استفاده می‌کند و `Tasks` به Project وابسته است.

هیچ ماژول Contact، Customer، Media، Ticket، Report، Setting یا Role/Permission در برنامه وجود ندارد.

## دسترسی

Authorization عمداً ساده است:

- `users.is_admin = true`: دسترسی مدیریتی کامل
- کاربر عادی: فقط پروژه‌هایی که عضو آن‌هاست
- Task برای کاربر عادی فقط وقتی قابل مشاهده است که Task متعلق به یکی از پروژه‌های عضو شدهٔ او باشد

عضویت پروژه فقط در جدول `project_user` نگهداری می‌شود.

## مدل داده

```text
User N <-> N Project
Project 1 -> N Task
```

هیچ assignee جداگانه‌ای برای Task وجود ندارد. دسترسی Task از عضویت در Project به دست می‌آید.
